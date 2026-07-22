<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    protected $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
    }

    public function index()
    {
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true, true);
        }

        $files = File::files($this->backupPath);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'raw_size' => $file->getSize(),
                    'created_at' => date('d-m-Y H:i:s', $file->getMTime()),
                    'timestamp' => $file->getMTime(),
                ];
            }
        }

        // Sort by newest first
        usort($backups, function ($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        return view('backup.index', compact('backups'));
    }

    public function create()
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true, true);
        }

        try {
            $driver = DB::getDriverName();
            $pdo = DB::connection()->getPdo();
            
            if ($driver === 'sqlite') {
                $tables = array_map('current', DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"));
            } else {
                $tables = array_map('current', DB::select('SHOW TABLES'));
            }
            
            $sql = "-- Backup Apotek Almaira\n";
            $sql .= "-- Generate Date: " . now()->format('Y-m-d H:i:s') . "\n";
            $sql .= "-- Database Driver: " . $driver . "\n";
            
            if ($driver === 'sqlite') {
                $sql .= "PRAGMA foreign_keys = OFF;\n\n";
            } else {
                $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            }

            foreach ($tables as $table) {
                if ($driver === 'sqlite' && ($table === 'sqlite_sequence' || $table === 'sqlite_stat1')) {
                    continue;
                }
                
                // Drop table statement
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                
                // Create table statement
                if ($driver === 'sqlite') {
                    $createObj = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='{$table}'")[0];
                    $sql .= $createObj->sql . ";\n\n";
                } else {
                    $createObj = DB::select("SHOW CREATE TABLE `{$table}`")[0];
                    $createSqlField = 'Create Table';
                    $createSqlObj = (array) $createObj;
                    $sql .= $createSqlObj[$createSqlField] . ";\n\n";
                }

                // Inserts
                $rows = DB::table($table)->get();
                if ($rows->count() > 0) {
                    $sql .= "-- Data for table `{$table}`\n";
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $columns = array_keys($rowArray);
                        
                        $escapedColumns = array_map(function($col) {
                            return "`{$col}`";
                        }, $columns);

                        $values = array_map(function ($val) use ($pdo) {
                            if ($val === null) return 'NULL';
                            return $pdo->quote($val);
                        }, $rowArray);

                        $sql .= "INSERT INTO `{$table}` (" . implode(', ', $escapedColumns) . ") VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }

            if ($driver === 'sqlite') {
                $sql .= "PRAGMA foreign_keys = ON;\n";
            } else {
                $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
            }

            $filename = 'backup-apotek-almaira-' . now()->format('Y-m-d-H-i-s') . '.sql';
            $filePath = $this->backupPath . '/' . $filename;

            File::put($filePath, $sql);

            // Trigger Backup notification alert
            \App\Services\NotificationService::triggerBackupAlert($filename, File::size($filePath));

            ActivityLogService::log(
                'BACKUP_CREATE',
                'Backup',
                "Membuat backup database berhasil: {$filename}"
            );

            return redirect()->route('backup.index')->with('toast_success', 'Backup database berhasil dibuat!');
        } catch (\Exception $e) {
            ActivityLogService::log(
                'BACKUP_ERROR',
                'Backup',
                "Gagal membuat backup database: " . $e->getMessage()
            );
            return redirect()->route('backup.index')->with('toast_error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        $filename = basename($filename);
        $filePath = $this->backupPath . '/' . $filename;

        if (File::exists($filePath)) {
            ActivityLogService::log(
                'BACKUP_DOWNLOAD',
                'Backup',
                "Mengunduh backup database: {$filename}"
            );
            return Response::download($filePath);
        }

        return redirect()->route('backup.index')->with('toast_error', 'File backup tidak ditemukan!');
    }

    public function destroy($filename)
    {
        $filename = basename($filename);
        $filePath = $this->backupPath . '/' . $filename;

        if (File::exists($filePath)) {
            File::delete($filePath);
            ActivityLogService::log(
                'BACKUP_DELETE',
                'Backup',
                "Menghapus backup database: {$filename}"
            );
            return redirect()->route('backup.index')->with('toast_success', 'File backup berhasil dihapus!');
        }

        return redirect()->route('backup.index')->with('toast_error', 'File backup tidak ditemukan!');
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
