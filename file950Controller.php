<?php

namespace App\Http\Controllers;

use App\Models\FileMT950;
use App\Models\Tag_61;
use Illuminate\Http\Request;

class file950Controller extends Controller
{
    public function list950()
    {
        $test_file_MT950 = FileMT950::orderBy('date', 'DESC')->paginate(100);
        return view('view_950' ,compact('test_file_MT950'));
    }

    public function extrait(Request $request)
    {
        
        $dateDebut = $request['dateDebut'];
        $dateFin = $request['dateFin'];
        //dd($dateFin);
        $transaction = $request['transaction'];
        $test_file_MT950 = FileMT950::whereBetween('date',[$dateDebut,$dateFin])->orderBy('date', 'DESC')->get();
        return view('recherche_950', compact('test_file_MT950'));
    }

    public function refresh950(){
        
        $files = fetchFiles(fetchRecusPath(true), '*.atldout');

        foreach($files as $file){

            $fileName = basename($file);
            $date = Date(filemtime($file));

            $extractedDetails  = extractSwiftDetails($file);

            foreach ($extractedDetails as $transaction) {
                if($transaction['bankInfo']['type'] == '950'){

                    $recus = new FileMT950();

                    $recus->type = $transaction['bankInfo']['type'];
                    $recus->index = $transaction['bankInfo']['index'];
                    $recus->filename = $fileName;
                    $recus->date = $date;
                    $recus->rows = fileToString($file);
                    $recus->trans_ref = $transaction['transactionNumber'];
                    $recus->sender = $transaction['bankInfo']['bankInfos'];

                    $recus->save();
                               
                    if(array_key_exists('950_61', $transaction)){
                        foreach($transaction['950_61'] as $trans){
                            $tag61 = new Tag_61();
                            $tag61->entr_statement_new = ':61:';
                            $tag61->value_statement_new = $trans['value'];
                            $tag61->code_statement = $trans['detail'];
                            $tag61->id_mt950 = $recus->id;

                            $tag61->save();
                        }
                    }
                    archiveFile($file, $fileName, archiveRecusPath(true));
                }  
            }
        }
        return redirect('/list950');
    }
}
