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
        $this->termFilm();

        return redirect()->route('view.term');
    }
    
    public function termFilm() {
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
    }

    public function toDfIdf()
    {
        $this->termFilm();
        $term = DB::select('SELECT * FROM terms');

        foreach ($term as $terms) {
            $id_term = $terms->id;
            $df = DB::table('tf_idfs')->where('id_term', '=', $id_term)->count();
            $jmlFilm = DB::table('films')->count();
            $jmlFilm = $jmlFilm + 1;
            $idf = log($jmlFilm / $df);

            DB::table('terms')->where('id', $id_term)->update(['df' => $df, 'idf' => $idf]);
        }
    }

    public function toTfIdf()
    {
        $this->toDfIdf();
        $tfIdf = DB::select('SELECT * FROM tf_idfs');

        foreach ($tfIdf as $tfIdfs) {
            $id_terms = $tfIdfs->id_term;
            $tf = $tfIdfs->tf;
            $term = DB::table('terms')->where('id', $id_terms)->get();

            foreach ($term as $terms) {
                $idf = $terms->idf;
                $tfIdf = $tf * $idf;
                DB::table('tf_idfs')->where('id_term', $id_terms)->where('tf', $tf)->update(['tf_idf' => $tfIdf]);
            }
        }
    }

    // update data tf-idf-kuadrat ke masing - masing id_term
    public function toTfIdfKuadrat()
    {
        $this->toTfIdf();
        $tfIdf = DB::select('SELECT * FROM tf_idfs');

        foreach ($tfIdf as $tfIdfs) {
            $tfIdf = $tfIdfs->tf_idf;
            $id_term = $tfIdfs->id_term;
            $tf = $tfIdfs->tf;
            $tfIdfKuadrat = $tfIdf * $tfIdf;

            DB::table('tf_idfs')->where('id_term', $id_term)->where('tf', $tf)->update(['tf_idf_kuadrat' => $tfIdfKuadrat]);
        }
    }
}
