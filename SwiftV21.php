<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

// Database Configuration
$host = "localhost";
$dbname = "swift";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $filter_sender = $_POST['sender'] ?? '';
    $filter_currency = $_POST['currency'] ?? '';
    $filter_start_date = $_POST['start_date'] ?? '';
    $filter_end_date = $_POST['end_date'] ?? '';

    // Convert Dates to SWIFT Format (YYMMDD)
    $filter_start_date_swift = date("ymd", strtotime($filter_start_date));
    $filter_end_date_swift = date("ymd", strtotime($filter_end_date));
  
    // Fetch SWIFT Messages from Database
    // Construct SQL Query to Filter Data Efficiently
    $sql = "SELECT id, content 
    FROM transactions
    WHERE content LIKE CONCAT('%F01', :sender, '%')  
    AND content REGEXP CONCAT('[CD][0-9]{6}', :currency)";
    // Prepare and Execute the Query
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':sender', $_POST['sender'], PDO::PARAM_STR);
    $stmt->bindParam(':currency', $_POST['currency'], PDO::PARAM_STR);
    $stmt->execute();

    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Define Headers
    $headers = ["Champ", "DC Mark", "Date", "Sender", "Receiver", "Currency", "Amount", "Value Date", "Entry Date", "Debit Card Mark", "Transaction Amount", "Reference for Account Owner", "Reference for Account Servicing Institution"];
    $columnIndex = 'A';
    // Retrieve Filters from POST Request

    // Add Headers to First Row
    foreach ($headers as $header) {
        $sheet->setCellValue($columnIndex . '1', $header);
        $columnIndex++;
    }

    $rowIndex = 2; // Start from second row

    foreach ($records as $record) {
        $content = $record['content'];

        // Step 1: Split content by '$' (each part is a separate SWIFT message)
        $swift_messages = explode('$', $content);
        foreach ($swift_messages as $swift_message) {

            $swift_message = trim($swift_message);

            if (empty($swift_message)) {
                continue; // Skip empty messages
            }
                        preg_match('/:60(M|F):([A-Z])(\d{6})([A-Z]{3})([\d,]+)/', $swift_message, $match);
                        $formatted_date =$match[3];

                        if(($formatted_date>=$filter_start_date_swift)&&($formatted_date<=$filter_end_date_swift)){
                        
                                // Extract Sender (From {1:})
                                preg_match('/\{1:F\d{2}([A-Z]{12})/', $swift_message, $match);
                                $sender = $match[1] ?? '';

                                // Extract Receiver (From {2:})
                                preg_match('/\{2:O\d{3}\d{10}([A-Z]{12})/', $swift_message, $match);
                                $receiver = $match[1] ?? '';

                                // Extract Opening Balance (`60F`)
                                preg_match('/:60(M|F):([A-Z])(\d{6})([A-Z]{3})([\d,]+)/', $swift_message, $match);
                                if (!empty($match)) {
                                    
                                    
                                        $sheet->setCellValue('A' . $rowIndex, "60F"); // Champ
                                        $sheet->setCellValue('B' . $rowIndex, $match[2]); // DC Mark (C/D)
                                        $formatted_date = "20" . substr($match[3], 0, 2) . "-" . substr($match[3], 2, 2) . "-" . substr($match[3], 4, 2);
                                        $sheet->setCellValue('C' . $rowIndex, $formatted_date); // Date (YYYY-MM-DD)
                                        $sheet->setCellValue('D' . $rowIndex, $sender); // Sender
                                        $sheet->setCellValue('E' . $rowIndex, $receiver); // Receiver
                                        $sheet->setCellValue('F' . $rowIndex, $match[4]); // Currency
                                        $sheet->setCellValue('G' . $rowIndex, str_replace(',', '.', $match[5])); // Amount
                                        $rowIndex++;
                                    
                                }

                                // Extract Transactions (`61`)
                                preg_match_all('/:61:(\d{6})(\d{4})?(C|CD|CR|DR|DD|D)([\d,]+)(.{4})(.*?)(?=\s+:61:|:62F:)/s', $swift_message, $matches, PREG_SET_ORDER);
                                foreach ($matches as $match) {
                                    preg_match('/([A-Za-z0-9- ]+)\s*\/\/\s*([A-Za-z0-9]+)/', $match[6], $ref_match);
                                    
                                    $formatted_date = "20" . substr($match[1], 0, 2) . "-" . substr($match[1], 2, 2) . "-" . substr($match[1], 4, 2);
                                    $sheet->setCellValue('H' . $rowIndex,$formatted_date); // Value Date (YYYY-MM-DD)
                                    $sheet->setCellValue('I' . $rowIndex, substr($match[2], 0, 2) . "/" . substr($match[2], 2, 2)); // Entry Date (MM/DD)
                                    $sheet->setCellValue('J' . $rowIndex, $match[3]); // Debit Card Mark (C/D)
                                    $sheet->setCellValue('K' . $rowIndex, str_replace(',', '.', $match[4])); // Amount
                                    $sheet->setCellValue('L' . $rowIndex, $ref_match[1] ?? ""); // Reference for Account Owner
                                    $sheet->setCellValue('M' . $rowIndex, $ref_match[2] ?? ""); // Reference for Account Servicing Institution
                                    $rowIndex++;
                                }

                                // Extract Closing Balance (`62F`)
                                preg_match('/:62(F|M):([A-Z])(\d{6})([A-Z]{3})([\d,]+)/', $swift_message, $match);
                                if (!empty($match)) {
                                    $sheet->setCellValue('A' . $rowIndex, "62".$match[1]); // Champ
                                    $sheet->setCellValue('B' . $rowIndex, $match[2]); // DC Mark (C/D)
                                    $formatted_date = "20" . substr($match[3], 0, 2) . "-" . substr($match[3], 2, 2) . "-" . substr($match[3], 4, 2);
                                    $sheet->setCellValue('C' . $rowIndex,$formatted_date); // Date (YYYY-MM-DD)
                                    $sheet->setCellValue('D' . $rowIndex, $sender); // Sender
                                    $sheet->setCellValue('E' . $rowIndex, $receiver); // Receiver
                                    $sheet->setCellValue('F' . $rowIndex, $match[4]); // Currency
                                    $sheet->setCellValue('G' . $rowIndex, str_replace(',', '.', $match[5])); // Amount
                                    $rowIndex++;
                                }
                        } 
                    }
    }

    // Save CSV File
  $filename = "{$filter_sender}{$filter_currency}{$filter_start_date_swift}_{$filter_end_date_swift}.csv";
    

    $writer = new Csv($spreadsheet);
    $writer->setDelimiter(',');
    $writer->setEnclosure('"');
    $writer->setLineEnding("\r\n");
    $writer->setSheetIndex(0);
    $writer->save($filename);

    // Force Download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    readfile($filename);
    unlink($filename);
    exit(); 

} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
