<?php

namespace Database\Seeders;

use App\Models\File;
use App\Models\Member;
use App\Models\MigrationError;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $inspected = 0;
        $faultyMembers = [];
        foreach (Member::lazy() as $member) {
            $inspected++;
            if (preg_match("/[a-z]/i", $member->dossier)) {
                MigrationError::create([
                    'from_table_type' => Member::class,
                    'from_table_id' => $member->id,
                    'to_table_type' => File::class,
                    'model_title' => $member->nom,
                    'data' => $member->toArray(),
                    'error_message' => ["Contains text in file number (dossier)"],
                    'error_title' => "Malformulated file number (dossier)",
                ]);

                $faultyMembers[] = $member;

                continue;
            }

            $number = ltrim($member->dossier, '0');
            // dd([strstr($number, '/', true), $member]);
            try {
                if (strstr($number, '/', true)) {
                    if (strlen(strstr($number, '/', true)) > 4) {
                        MigrationError::create([
                            'from_table_type' => Member::class,
                            'from_table_id' => $member->id,
                            'to_table_type' => File::class,
                            'model_title' => $member->nom,
                            'data' => $member->toArray(),
                            'error_message' => ["File number (dossier) contains " . strlen(strstr($number, '/', true)) . " rather than 4"],
                            'error_title' => "File number (dossier) too long",
                        ]);

                        $faultyMembers[] = $member;

                        continue;
                    }
                    // dd([$number, $member]);
                    $number =  sprintf("%04d", strstr($number, '/', true));
                    // dd([$number, $member, 'uuu']);
                } else if (strstr($number, '-', true)) {
                    if (strlen(strstr($number, '-', true)) > 4) {
                        MigrationError::create([
                            'from_table_type' => Member::class,
                            'from_table_id' => $member->id,
                            'to_table_type' => File::class,
                            'model_title' => $member->nom,
                            'data' => $member->toArray(),
                            'error_message' => ["File number (dossier) contains " . strlen(strstr($number, '-', true)) . " rather than 4"],
                            'error_title' => "File number (dossier) too long",
                        ]);

                        $faultyMembers[] = $member;

                        continue;
                    }
                    $number =  sprintf("%04d", strstr($number, '-', true));
                } else {
                    if ($number > 4) {
                        MigrationError::create([
                            'from_table_type' => Member::class,
                            'from_table_id' => $member->id,
                            'to_table_type' => File::class,
                            'model_title' => $member->nom,
                            'data' => $member->toArray(),
                            'error_message' => ["File number (dossier) contains " . strlen($number) . " rather than 4"],
                            'error_title' => "File number (dossier) too long",
                        ]);

                        $faultyMembers[] = $member;

                        continue;
                    } else {
                        $number =  sprintf("%04d", $number);
                    }
                }
            } catch (\Throwable $th) {
                dd([$th, $member]);
            }

            // dd($number);

            $sex = null;

            if ($member->sexe == "M") {
                $sex = "Male";
            } else if ($member->sexe == "F") {
                $sex = "Female";
            }

            $yearOfBirth = null;

            if (!preg_match("/[a-z]/i", $member->anneenaissance)) {
                $yearOfBirth = $member->anneenaissance;
            }

            $registrationDate = $member->date;

            if (strlen($registrationDate) == 8) {
                $explodedDate = explode('-', $registrationDate);
                $registrationDate = date("Y-m-d", mktime(0, 0, 0, $explodedDate[0], $explodedDate[1], $explodedDate[2]));
            }

            if (strstr($registrationDate, '/')) {
                $explodedDate = explode('/', $registrationDate);
                $registrationDate = date("Y-m-d", mktime(0, 0, 0, $explodedDate[1], $explodedDate[0], strlen($explodedDate[2]) == 4 ? $explodedDate[2] : $member->year));
            }

            if (!preg_match("/[1-9]/i", $registrationDate)) {
                $registrationDate = null;
            }

            try {
                $file = File::create([
                    'number' => $number,
                    'names' => $member->nom,
                    'sex' => $sex,
                    'year_of_birth' => $yearOfBirth,
                    'phone_number' => $member->phone,
                    'registration_date' => $registrationDate,
                    'registration_year' => $member->year,
                    'location' => [
                        'province_id' => $member->province,
                        'district_id' => $member->district,
                        'sector_id' => $member->sector,
                        'cell_id' => $member->cell,
                        'village_id' => $member->village
                    ],
                    'legacy_db_member_id' => $member->id,
                    'created_at' => $member->created_at,
                    'updated_at' => $member->updated_at,
                ]);

                try {
                    $file->linkedInsurances()->createMany($this->getInsuranceData($member));
                    if (strlen($member->nom_personne) > 0 || strlen($member->tel_personne)) {
                        $file->emergencyContacts()->create([
                            'name' => $member->nom_personne,
                            'phone_number' => $member->tel_personne,
                            'enabled' => 1
                        ]);
                    }
                } catch (\Throwable $th) {
                    dd($th);
                }

                $member->migrated = true;
                $member->migrated_to = $file->id;

                $member->save();
            } catch (\Throwable $th) {
                if (strstr($th->getMessage(), 'SQLSTATE[HY000]: General error: 1366 Incorrect integer value')) {
                    MigrationError::create([
                        'from_table_type' => Member::class,
                        'from_table_id' => $member->id,
                        'to_table_type' => File::class,
                        'model_title' => $member->nom,
                        'data' => $member->toArray(),
                        'error_message' => $th,
                        'error_title' => "No file number (dossier)",
                    ]);

                    $faultyMembers[] = $member;
                } else if (strstr($th->getMessage(), 'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry')) {
                    MigrationError::create([
                        'from_table_type' => Member::class,
                        'from_table_id' => $member->id,
                        'to_table_type' => File::class,
                        'model_title' => $member->nom,
                        'data' => $member->toArray(),
                        'error_message' => $th,
                        'error_title' => "Duplicate",
                    ]);

                    $faultyMembers[] = $member;
                } else {
                    dd([$th, $member]);
                }
            }
        }
    }

    public function getInsuranceData(Member $member)
    {
        $insuranceData = [];

        if ($member->assureur == "PRIVATE" || $member->assureur == "Prive(BP)" || $member->assureur == "RPRIVEE" || $member->assureur == "PRIVEEE" || $member->assureur == "PRIVEE" || $member->assureur == "PRIEE" || $member->assureur == "PRIVE" || $member->assureur == "PRIVEE`") {
            $insuranceData[] = ['insurance_id' => 3];
        } else if ($member->assureur == "RSSB") {
            $insuranceData[] = [
                'insurance_id' => 4,
                'specific_data' => [
                    'member_number' => $member->memberno,
                    'beneficiary' => $member->bentype,
                    'affiliate_name' => $member->principal,
                    'affiliate_affectation' => $member->affectation,
                ]
            ];
        } else if ($member->assureur == "MMI" || $member->assureur == "M M I" || $member->assureur == "MMII" || $member->assureur == "MM" || $member->assureur == "MIMI") {
            $insuranceData[] = [
                'insurance_id' => 5,
                'specific_data' => [
                    'affiliation_number' => $member->memberno,
                    'category_of_beneficiary' => $member->bentype,
                ]
            ];
        } else if ($member->assureur == "URWEGO OPP" || $member->assureur == "UOB" || $member->assureur == "URWEGO" || $member->assureur == "U O B" || $member->assureur == "UB") {
            $insuranceData[] = [
                'insurance_id' => 8,
                'specific_data' => [
                    'affiliate_name' => $member->principal,
                    'is_affiliated' => $member->principal == $member->nom,
                ]
            ];
        } else if ($member->assureur == "SORAS") {
            $insuranceData[] = [
                'insurance_id' => 6,
                'specific_data' => [
                    'police_number' => $member->policeno,
                    'affiliation_number' => $member->memberno,
                ]
            ];
        } else if ($member->assureur == "RADIANT" || $member->assureur == "rad") {
            $insuranceData[] = [
                'insurance_id' => 12,
                'specific_data' => [
                    'police_number' => $member->policeno,
                    'affiliation_number' => $member->memberno,
                ]
            ];
        } else if ($member->assureur == "SAHAM" || $member->assureur == "SAMAM") {
            $insuranceData[] = [
                'insurance_id' => 9,
                'specific_data' => [
                    'police_number' => $member->policeno,
                    'affiliation_number' => $member->memberno,
                ]
            ];
        } else if ($member->assureur == "BRITAM") {
            $insuranceData[] = [
                'insurance_id' => 7,
                'specific_data' => [
                    'police_number' => $member->policeno,
                ]
            ];
        } else if ($member->assureur == "PRIME INSURANCE" || $member->assureur == "PRIME") {
            $insuranceData[] = [
                'insurance_id' => 15,
                'specific_data' => [
                    'affiliation_number' => $member->memberno,
                ]
            ];
        } else if ($member->assureur == "EQUITY BANK") {
            $insuranceData[] = [
                'insurance_id' => 13,
                'specific_data' => [
                    'affiliation_number' => $member->memberno,
                ]
            ];
        } else if ($member->assureur == "ASA- MICROFINANCE") {
            $insuranceData[] = [
                'insurance_id' => 14,
                'specific_data' => [
                    'affiliation_number' => $member->memberno,
                ]
            ];
        } else if ($member->assureur == "UAP" || $member->assureur == "RWANDA BAREAU ASSOCI") {
            $insuranceData[] = [
                'insurance_id' => 11,
                'specific_data' => [
                    'member_number' => $member->memberno,
                ]
            ];
        }

        if (count($insuranceData) == 0) {
            $insuranceData[] = ['insurance_id' => 3];
        }

        return $insuranceData;
    }
}
