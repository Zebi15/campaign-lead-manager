<?php

namespace App\Imports;

use App\Models\Lead;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

class LeadImport implements ToCollection, WithHeadingRow
{
    protected $campaignId;
    protected $failures = [];
    protected $importedCount = 0;
    protected $phoneUtil;

    public function __construct($campaignId)
    {
        $this->campaignId = $campaignId;
        $this->phoneUtil = PhoneNumberUtil::getInstance();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Prepare data for validation
            $data = $row->toArray();
            $validationErrors = [];
            
            // Validate required fields
            if (empty($data['name'])) {
                $validationErrors[] = 'Name is required';
            }
            
            if (empty($data['email'])) {
                $validationErrors[] = 'Email is required';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $validationErrors[] = 'Invalid email format';
            }
            
            // Validate phone number using libphonenumber
            if (empty($data['phone_number'])) {
                $validationErrors[] = 'Phone number is required';
            } else {
                // Clean and format the phone number
                $phoneNumber = $data['phone_number'];
                $phoneNumber = preg_replace('/[^\d+]/', '', $phoneNumber);
                
                // Add + if not present
                if (!str_starts_with($phoneNumber, '+')) {
                    $phoneNumber = '+' . $phoneNumber;
                }
                
                // Validate the phone number
                try {
                    $parsedNumber = $this->phoneUtil->parse($phoneNumber, null);
                    if (!$this->phoneUtil->isValidNumber($parsedNumber)) {
                        $validationErrors[] = 'The phone number is not valid according to international standards';
                    } else {
                        // Update with formatted phone number
                        $data['phone_number'] = $phoneNumber;
                    }
                } catch (NumberParseException $e) {
                    $validationErrors[] = 'The phone number format is incorrect: ' . $e->getMessage();
                }
            }
            
            // If there are validation errors, add to failures and skip
            if (!empty($validationErrors)) {
                $this->failures[] = [
                    'row' => $row->toArray(),
                    'errors' => $validationErrors
                ];
                continue;
            }

            // Create the lead with validated data
            Lead::create([
                'campaign_id' => $this->campaignId,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'],
            ]);

            $this->importedCount++;
        }
    }

    public function getFailures()
    {
        return $this->failures;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }
}