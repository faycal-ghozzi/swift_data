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
function extractBalances($swift_message) {
    $balances = [
       "Extracted Number"=>"",
        "Champ" => "",
        "DC Mark" => "",
        "Date" => "",
        "Sender" => "",
        "Receiver" => "",
        "Currency" => "",
        "Amount" => "",
        "ChampC" => "",
        "DC Mark C" => "",
        "Date C" => "",
        "Amount C" => "",
        "Transactions" => [],
    ];

    // Extract Receiver (From {1:})
    preg_match('/\{1:F\d{2}([A-Z]{12})/', $swift_message, $match_sender);
    $balances["Receiver"] = $match_sender[1] ?? '';

    // Extract Sender (From {2:})
    preg_match('/\{2:O\d{3}\d{10}([A-Z]{12})/', $swift_message, $match_receiver);
    $balances["Sender"] = $match_receiver[1] ?? '';

    // Extract Opening Balance (`60F`)
    preg_match('/:60(M|F):([A-Z])(\d{6})([A-Z]{3})([\d,]+)/', $swift_message, $match_ob);
    if (!empty($match_ob)) {
        $balances["Champ"] = "60" . $match_ob[1]; // 60F or 60M
        $balances["DC Mark"] = $match_ob[2]; // C/D
        $balances["Date"] = "20" . substr($match_ob[3], 0, 2) . "-" . substr($match_ob[3], 2, 2) . "-" . substr($match_ob[3], 4, 2);
        $balances["Currency"] = $match_ob[4];
        $balances["Amount"] = str_replace(',', '.', $match_ob[5]);
    }

    // Extract Closing Balance (`62F`)
    preg_match('/:62(F|M):([A-Z])(\d{6})([A-Z]{3})([\d,]+)/', $swift_message, $match_cb);
    if (!empty($match_cb)) {
        $balances["ChampC"] = "62" . $match_cb[1]; // 62F or 62M
        $balances["DC Mark C"] = $match_cb[2]; // C/D
        $balances["Date C"] = "20" . substr($match_cb[3], 0, 2) . "-" . substr($match_cb[3], 2, 2) . "-" . substr($match_cb[3], 4, 2);
        $balances["Amount C"] = str_replace(',', '.', $match_cb[5]);
    }
    preg_match('/:20:([A-Za-z0-9]+)/', $swift_message, $match);
   $balances["Extracted Number"] =  $match[1];
    // Extract Transactions (`:61:`) using `extract61()` function
    $balances["Transactions"] = extract61($swift_message);
    return $balances; // Returns array with extracted data
}
function sortBalancesByExtractedNumber(&$balances_array) {
    usort($balances_array, function ($a, $b) {
        $numA = isset($a['Extracted Number']) ? (string) $a['Extracted Number'] : '';
        $numB = isset($b['Extracted Number']) ? (string) $b['Extracted Number'] : '';
        return strcmp($numA, $numB);
    });
}

function extract61($swift_message) {
    $transactions = []; // Array to store extracted transactions

    preg_match_all('/:61:(\d{6})(\d{4})?(C|CD|CR|DR|DD|D)([\d,]+)(.{4})(.*?)(?=\s+:61:|:62F:)/s', $swift_message, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        preg_match('/([A-Za-z0-9- ]+)\s*\/\/\s*([A-Za-z0-9]+)/', $match[6], $ref_match);

        $formatted_date = "20" . substr($match[1], 0, 2) . "-" . substr($match[1], 2, 2) . "-" . substr($match[1], 4, 2);
        $entry_date = isset($match[2]) ? substr($match[2], 0, 2) . "/" . substr($match[2], 2, 2) : ""; // Handle null entry date

        // Store extracted data in an array
        $transactions[] = [
            "Value Date" => $formatted_date, // YYYY-MM-DD
            "Entry Date" => $entry_date, // MM/DD (if available)
            "Debit Card Mark" => $match[3], // C/D/CD/DR/DD
            "Amount" => str_replace(',', '.', $match[4]), // Convert amount format
            "Reference for Account Owner" => $ref_match[1] ?? "", // Extracted reference
            "Reference for Account Servicing Institution" => $ref_match[2] ?? "" // Extracted institution reference
        ];
    }

    return $transactions; // Return array of transactions
}


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
    WHERE content REGEXP CONCAT('\\\\{2:O[0-9]{3}[0-9]{4}[0-9]{6}', :sender)
    AND content REGEXP CONCAT('[CD][0-9]{6}', :currency)";
    // Prepare and Execute the Query
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':sender', $_POST['sender'], PDO::PARAM_STR);
    $stmt->bindParam(':currency', $_POST['currency'], PDO::PARAM_STR);
    $stmt->execute();

    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $balances_array = [];

     
 
    foreach ($records as $record) {
        $content = $record['content'];
        
       
        // Step 1: Split content by '$' (each part is a separate SWIFT message)
        $swift_messages = explode('$', $content);
        
        foreach ($swift_messages as $swift_message) {

            $swift_message = trim($swift_message);

            if (empty($swift_message)) {
                continue; // Skip empty messages
            }
            preg_match('/:60(M|F):([A-Z])(\d{6})([A-Z]{3})([\d,]+)/', $swift_message, $match_ob);
            $formatted_date1 =$match_ob[3];
            
           // exit();
            if(($formatted_date1>=$filter_start_date_swift)&&($formatted_date1<=$filter_end_date_swift)){
               $balances_array[] = extractBalances($swift_message);
            
            }
        }            
    }
    sortBalancesByExtractedNumber($balances_array);

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
    // Start from second row
    $rowIndex = 2;
    
    // Write data from the sorted balances array to Excel
foreach ($balances_array as $balance) {
    // Fill Opening 
    $sheet->setCellValue('A' . $rowIndex, $balance["Champ"]);
    $sheet->setCellValue('B' . $rowIndex, $balance["DC Mark"]);
    $sheet->setCellValue('C' . $rowIndex, $balance["Date"]);
    $sheet->setCellValue('D' . $rowIndex, $balance["Sender"]);
    $sheet->setCellValue('E' . $rowIndex, $balance["Receiver"]);
    $sheet->setCellValue('F' . $rowIndex, $balance["Currency"]);
    $sheet->setCellValue('G' . $rowIndex, $balance["Amount"]);

    $rowIndex++;
    // Write Transactions (from :61:)
    if (!empty($balance["Transactions"])) {
        foreach ($balance["Transactions"] as $transaction) {
            // Insert transaction details into columns
            $sheet->setCellValue('H' . $rowIndex, $transaction["Value Date"]);
            $sheet->setCellValue('I' . $rowIndex, $transaction["Entry Date"]);
            $sheet->setCellValue('J' . $rowIndex, $transaction["Debit Card Mark"]);
            $sheet->setCellValue('K' . $rowIndex, $transaction["Amount"]);
            $sheet->setCellValue('L' . $rowIndex, $transaction["Reference for Account Owner"]);
            $sheet->setCellValue('M' . $rowIndex, $transaction["Reference for Account Servicing Institution"]);
            $rowIndex++; // Move to the next row for the next transaction
        }
    } 
    $sheet->setCellValue('A' . $rowIndex, $balance["ChampC"]);
    $sheet->setCellValue('B' . $rowIndex, $balance["DC Mark C"]);
    $sheet->setCellValue('C' . $rowIndex, $balance["Date C"]);
    $sheet->setCellValue('D' . $rowIndex, $balance["Sender"]);
    $sheet->setCellValue('E' . $rowIndex, $balance["Receiver"]);
    $sheet->setCellValue('F' . $rowIndex, $balance["Currency"]);
    $sheet->setCellValue('G' . $rowIndex, $balance["Amount C"]);
        
    $rowIndex++;
    
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