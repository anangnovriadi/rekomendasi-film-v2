<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sastrawi\Tokenizer\TokenizerFactory;
use DB;
use App\Model\Film;

class FilmTermController extends Controller
{
    public function view() {
        $term = DB::select('SELECT * FROM terms');
        $countTerm = DB::table('terms')->count();
        $countFilm = DB::table('films')->count();
        
        return view('admin.term.view', compact('term', 'countTerm', 'countFilm'));
    }

    public function getFilm()
    {
        $film = Film::all();
        return $film;
    }
    
    public function term() {
        set_time_limit(0);
        $getFilm = $this->getFilm();

        foreach ($getFilm as $allFilm) {
            $tokenisasi = new TokenizerFactory;
            $tokenizer = $tokenisasi->createDefaultTokenizer();

            $id_film = $allFilm->id;
            $nama_film = $tokenizer->tokenize($allFilm->nama_film);
            $genre_film = $tokenizer->tokenize($allFilm->genre);
            $aktor_aktris = $tokenizer->tokenize($allFilm->aktor_aktris);
            $deskripsi = $tokenizer->tokenize($allFilm->deskripsi_film);

            $mergeAll = array_merge($nama_film, $genre_film, $aktor_aktris);
            foreach ($mergeAll as $terms) {
                if (strlen($terms) !== 0) {
                    $cekTerm = DB::table('terms')->where('nama_term', '=', $terms)->count();

                    if ($cekTerm == 0) {
                        $term = DB::insert('INSERT INTO terms(id, nama_term, df, idf) values (?, ?, ?, ?)', [null, $terms, 0, 0]);
                        $getIdTerm = DB::select('SELECT * from terms WHERE nama_term = ?', array($terms));

                        foreach ($getIdTerm as $getIdTerms) {
                            $id_term = $getIdTerms->id;
                        }

                        DB::insert('INSERT INTO tf_idfs(id, id_film, id_user, id_term, tf, tf_idf, tf_idf_kuadrat) values(?, ?, ?, ?, ?, ?, ?)', [null, $id_film, 0, $id_term, 1, 0, 0]);
                    } else {
                        $getIdTerm = DB::select('SELECT * from terms WHERE nama_term = ?', array($terms));

                        foreach ($getIdTerm as $getIdTerms) {
                            $id_term = $getIdTerms->id;
                        }

                        $cekTf = DB::table('tf_idfs')->where('id_term', '=', $id_term)->where('id_film', '=', $id_film)->count();
                        if ($cekTf == 0) {
                            DB::insert('INSERT INTO tf_idfs(id, id_film, id_user, id_term, tf, tf_idf, tf_idf_kuadrat) values(?, ?, ?, ?, ?, ?, ?)', [null, $id_film, 0, $id_term, 1, 0, 0]);
                        } else {
                            $getTf = DB::select('SELECT * from tf_idfs WHERE id_term = ? AND id_film = ?', array($id_term, $id_film));

                            foreach ($getTf as $tf) {
                                $frekuensi = $tf->tf;
                                $id_tf = $tf->id;
                            }

                            $frekuensi = $frekuensi + 1;
                            $updateTf = DB::table('tf_idfs')->where('id', '=', $id_tf)->update(['tf' => $frekuensi, 'id_term' => $id_term]);
                        }
                    }
                }
            }
        }

        return redirect()->route('view.term');
    }
}
