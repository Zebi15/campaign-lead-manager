<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Filament\Resources\LeadResource;
use App\Imports\LeadImport;
use App\Models\Campaign;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Actions\Action;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import')
                ->label('Import Leads')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Select::make('campaign_id')
                        ->label('Campaign')
                        ->options(Campaign::pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    FileUpload::make('file')
                        ->label('Excel/CSV File')
                        ->acceptedFileTypes([
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/csv'
                        ])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $filePath = Storage::disk('public')->path($data['file']);
                    
                    $import = new LeadImport($data['campaign_id']);
                    Excel::import($import, $filePath);
                    
                    // Handle failures and generate error report
                    $failures = $import->getFailures();
                    $importedCount = $import->getImportedCount();
                    
                    if (count($failures) > 0) {
                        // Generate a unique filename
                        $timestamp = time();
                        $filename = "lead-import-errors-{$timestamp}.csv";
                        
                        // Make sure the directory exists
                        $directory = public_path('downloads');
                        if (!file_exists($directory)) {
                            mkdir($directory, 0755, true);
                        }
                        
                        // Full path for the CSV file
                        $csvPath = "{$directory}/{$filename}";
                        
                        // Create and populate the CSV
                        $csv = Writer::createFromPath($csvPath, 'w+');
                        
                        // Add headers including original columns plus error column
                        $firstFailure = $failures[0];
                        $headers = array_keys($firstFailure['row']);
                        $headers[] = 'Validation Error';
                        $csv->insertOne($headers);
                        
                        // Add rows with errors
                        foreach ($failures as $failure) {
                            $row = $failure['row'];
                            $row['Validation Error'] = implode(', ', $failure['errors']);
                            $csv->insertOne($row);
                        }
                        
                        // Use a URL that's directly accessible via the web server
                        $downloadUrl = url("downloads/{$filename}");
                        
                        Notification::make()
                            ->title('Import completed with errors')
                            ->body("Imported {$importedCount} leads successfully. " . count($failures) . " rows failed validation.")
                            ->actions([
                                Action::make('download')
                                    ->label('Download Error Report')
                                    ->url($downloadUrl)
                                    ->openUrlInNewTab(),
                            ])
                            ->warning()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Import completed successfully')
                            ->body("All {$importedCount} leads were imported successfully.")
                            ->success()
                            ->send();
                    }
                }),
        ];
    }
}