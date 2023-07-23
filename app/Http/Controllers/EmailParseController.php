<?php

namespace App\Http\Controllers;

use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use App\Models\LogEntry;
use Illuminate\Support\LazyCollection;
use App\Models\OperatorSmsHistory;
use File;

class EmailParseController extends Controller
{
    public function index()
    {
        $client = Client::account('default');
        $client->connect();

        // get all unseen messages from folder INBOX
        // $aMessage = $oClient->getUnseenMessages($oClient->getFolder('INBOX'));
        $folders = $client->getFolders();
        foreach ($folders as $folder) {

            //get all email in specific date
            // $messages = $folder->messages()->all()->since(date('d.m.Y'))->get(); //for specific date
            $messages = $folder->messages()->all()->get(); //for all folders
            //end
            foreach ($messages as $message) {
                echo $message->getSubject() . '<br />';
                echo 'Attachments: ' . $message->getAttachments()->count() . '<br />';
                $attachments = $message->getAttachments();
                if ($message->getAttachments()->count() > 0) {
                    foreach ($attachments as $attachment) {
                        $type = $attachment->getExtension();
                        //downloading pdf
                        if ($type == "pdf") {
                            // dd($attachment);
                            $file_name = $attachment->getFilename();
                            //save attachment
                            if (!File::exists($file_name) && !is_dir($file_name)) {
                                $attachment->save(storage_path('app/public/'), $file_name);
                            }
                            //end
                        }
                        //end
                        //downloading xlsx
                        if ($type == 'bin') {
                            $check_file = public_path('storage/') . urlencode($attachment->name);
                            //check if file exist or not
                            if (!File::exists($check_file) && !is_dir($check_file)) {
                                //save attachment
                                $attachment->save(storage_path('app/public/'), urlencode($attachment->name));
                                //end
                            }
                            //inserting into database
                            $this->saveFromExcel(urlencode($attachment->name));
                            //end
                            exit;
                        }
                        //end
                    }
                }
                // echo $message->getHTMLBody(); //. '< br />';

                // Move the current Message to another folder to in gmail
                // if ($message->move('Read_Inbox') == true) {
                //     echo 'Message has been moved';
                // } else {
                //     echo 'Message could not be moved';
                // }
            }
            // imap_close($client);
        }
    }
    public function saveFromExcel($file_name)
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(TRUE);
        $spreadsheet = $reader->load(public_path('storage/') . $file_name);

        $worksheet = $spreadsheet->getActiveSheet();
        // Get the highest row number and column letter referenced in the worksheet
        $highestRow = $worksheet->getHighestRow(); // e.g. 10
        $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
        // Increment the highest column letter
        $highestColumn++;
        $sms_records = [];
        for ($row = 5; $row <= $highestRow; ++$row) {
            $sms_records[$row] = array(
                'date' => $worksheet->getCell('B' . $row)->getValue(),
                'service_id' => $worksheet->getCell('C' . $row)->getValue(),
                'bu' => $worksheet->getCell('D' . $row)->getValue(),
                'type' => $worksheet->getCell('E' . $row)->getValue(),
                'service_name' => $worksheet->getCell('F' . $row)->getValue(),
                'total_sub_base' => $worksheet->getCell('G' . $row)->getValue(),
                'activation' => $worksheet->getCell('H' . $row)->getValue(),
                'renewal_count' => $worksheet->getCell('I' . $row)->getValue(),
                'deactivation' => $worksheet->getCell('J' . $row)->getValue(),
                'ppu_success_count' => $worksheet->getCell('K' . $row)->getValue(),
                'total_success_count' => $worksheet->getCell('L' . $row)->getValue()
            );
        }
        OperatorSmsHistory::insert($sms_records);
        echo 'Data inserted successfully';
    }
    // public function saveFromExcel($file_name)
    // {
    //     $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
    //     $reader->setReadDataOnly(TRUE);
    //     $spreadsheet = $reader->load(public_path('storage/') . $file_name);

    //     $worksheet = $spreadsheet->getActiveSheet();
    //     // Get the highest row number and column letter referenced in the worksheet
    //     $highestRow = $worksheet->getHighestRow(); // e.g. 10
    //     $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
    //     // Increment the highest column letter
    //     $highestColumn++;
    //     $sms_record = [];
    //     echo '<table>' . "\n";
    //     for ($row = 5; $row <= $highestRow; ++$row) {
    //         echo '<tr>' . PHP_EOL;
    //         for ($col = 'B'; $col != $highestColumn; ++$col) {
    //             echo '<td>' .
    //                 $worksheet->getCell($col . $row)
    //                 ->getValue() .
    //                 '</td>' . PHP_EOL;
    //             $sms_record[$col][$row] = $worksheet->getCell($col . $row)->getValue();
    //         }
    //         echo '</tr>' . PHP_EOL;
    //     }
    //     echo '</table>' . PHP_EOL;
    //     // dd($sms_record);
    //     OperatorSmsHistory::insert($sms_record);
    //     echo 'Data inserted successfully';
    // }
}
