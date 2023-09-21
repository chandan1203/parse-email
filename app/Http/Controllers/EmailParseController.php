<?php

namespace App\Http\Controllers;

use File;
use League\Csv\Writer;
use SplTempFileObject;
use App\Models\LogEntry;
use Spatie\PdfToText\Pdf;
use Smalot\PdfParser\Parser;
use Webklex\IMAP\Facades\Client;
use App\Models\OperatorSmsHistory;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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
                            $this->convert($file_name);
                            // $this->pdftoexcel();
                            // $this->pdftocsv();
                            exit;
                            //end
                        }
                        //end
                        //downloading xlsx
                        // if ($type == 'bin') {
                        //     $check_file = public_path('storage/') . urlencode($attachment->name);
                        //     //check if file exist or not
                        //     if (!File::exists($check_file) && !is_dir($check_file)) {
                        //         //save attachment
                        //         $attachment->save(storage_path('app/public/'), urlencode($attachment->name));
                        //         //end
                        //     }
                        //     //inserting into database
                        //     $this->saveFromExcel(urlencode($attachment->name));
                        //     //end
                        //     exit;
                        // }
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

    public function convert($file_name)
    {
        $pdfFile = public_path('storage/5. software shop limited ssl wireless_May-2023.pdf');

        // Define the path for the converted Excel file
        $excelFilePath = storage_path('app/public/converted_excel.xlsx');

        // Extract text from PDF
        $text = (new Pdf())
            ->setPdf($pdfFile)
            ->text();

            $lines = explode("\n", $text);

            // Create a new Excel spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Loop through the lines and set them in separate cells, starting from A1
            foreach ($lines as $row => $line) {
                $columns = explode("\t", $line);
                foreach ($columns as $col => $cell) {
                    $sheet->setCellValueByColumnAndRow($col + 1, $row + 1, $cell);
                }
            }

            // Save the Excel file
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($excelFilePath);

            // Return a download link for the converted Excel file
            return response()->download($excelFilePath, 'converted_excel.xlsx');
    }

    public function pdftoexcel()
    {
        $pdfFilePath = public_path('storage/5. software shop limited ssl wireless_May-2023.pdf');
        $excelFilePath = storage_path('app/public/converted_excel.xlsx');

        // Use the appropriate command for the PDF-to-Excel conversion tool
        $command = "pdftoexcel $pdfFilePath $excelFilePath";

        // Execute the command
        exec($command);

        // Return a download link for the converted Excel file
        return response()->download($excelFilePath, 'converted_excel.xlsx');
    }

    public function pdftocsv()
    {
                // Validate the uploaded PDF file

                // Get the uploaded PDF file
                $pdfFile = public_path('storage/5. software shop limited ssl wireless_May-2023.pdf');

                // Extract text from PDF
                $text = (new Pdf())
                    ->setPdf($pdfFile)
                    ->text();

                // Create a CSV writer
                $csv = Writer::createFromFileObject(new SplTempFileObject());

                // Split the text into lines and add them as rows to the CSV
                $lines = explode("\n", $text);
                foreach ($lines as $line) {
                    $csv->insertOne([$line]);
                }

                // Set the CSV headers
                $csv->output('converted.csv');

                // Return the CSV as a response
                return response()->stream(
                    function () use ($csv) {
                        $csv->output();
                    },
                    200,
                    [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => 'attachment; filename="converted.csv"',
                    ]
                );
    }
}
