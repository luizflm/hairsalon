<?php

namespace App\Http\Controllers;

use App\Models\Hairdresser;
use App\Models\HairdresserEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EvaluationController extends Controller
{
    public function getMyEvaluations() {
        $array = ['error' => '', 'list' => []];

        $idUser = Auth::user()->id;
        $evaluations = HairdresserEvaluation::where('id_user', $idUser)->get();
        foreach($evaluations as $evaluation) {
            $hairdresser = Hairdresser::find($evaluation->id_hairdresser);
            $hairdresser->avatar = asset('storage/'.$hairdresser->avatar);

            $array['list'][] = [
                'id' => $evaluation->id,
                'hairdresser' => $hairdresser,
                'stars' => $evaluation->stars,
                'comment' => $evaluation->comment,
            ];
        }

        return $array;
    }

    public function getOne($id) {
        $array = ['error' => ''];

        $evaluation = HairdresserEvaluation::find($id);
        if($evaluation) {
            $hairdresser = Hairdresser::find($evaluation->id_hairdresser);
            $hairdresser->avatar = asset('storage/'.$hairdresser->avatar);

            $array['data'] = [
                'id' => $evaluation->id,
                'hairdresser' => $hairdresser,
                'stars' => $evaluation->stars,
                'comment' => $evaluation->comment,
            ];
        } else {
            $array['error'] = 'Não encontrado.';
            return $array;
        }

        return $array;
    }

    public function getHairdresserEvaluations($id) {
        $array = ['error' => '', 'list' => []];

        $hairdresser = Hairdresser::find($id);
        if($hairdresser) {
            $evaluations = HairdresserEvaluation::where('id_hairdresser', $id)->get();
            foreach($evaluations as $evaluation) {
                $hairdresser = Hairdresser::find($evaluation->id_hairdresser);
                $hairdresser->avatar = asset('storage/'.$hairdresser->avatar);
    
                $array['list'][] = [
                    'id' => $evaluation->id,
                    'hairdresser' => $hairdresser,
                    'stars' => $evaluation->stars,
                    'comment' => $evaluation->comment,
                ];
            }
        } else {
            $array['error'] = 'Cabelereiro(a) não encontrado.';
            return $array;
        }

        return $array;
    }

    public function insert(Request $request) {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'id_hairdresser' => 'required',
            'stars' => 'required'
        ]);
        if(!$validator->fails()) {
            $idHairdresser = $request->id_hairdresser;
            $stars = $request->stars;
            $comment = $request->comment;
            $idUser = Auth::user()->id;

            $hairdresser = Hairdresser::find($idHairdresser);
            if($hairdresser) {
                $allowedStars = '1,2,3,4,5';
                $allowedStars = explode(',', $allowedStars);
                if(in_array($stars, $allowedStars)) {
                    $comment = $comment ?? '';

                    $newEvaluation = HairdresserEvaluation::create([
                        'id_user' => $idUser,
                        'id_hairdresser' => $idHairdresser,
                        'stars' => $stars,
                        'comment' => $comment,
                    ]);

                    $array['data'] = $newEvaluation;
                } else {
                    $array['error'] = 'Número de estrelas inválido.';
                    return $array;
                }
            } else {
                $array['error'] = 'O(a) cabelereiro(a) não foi encontrado.';
                return $array;
            }
        } else {
            $array['error'] = $validator->messages()->first();
            return $array;
        }

        return $array;
    }

    public function update($id, Request $request) {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'stars' => 'required'
        ]);
        if(!$validator->fails()) {
            $evaluation = HairdresserEvaluation::find($id);
            $stars = $request->stars;
            $comment = $request->comment;
            $comment = $comment ?? '';
            
            $evaluation->update([
                'stars' => $stars,
                'comment' => $comment,
            ]);

            $array['data'] = $evaluation;
        } else {
            $array['error'] = $validator->messages()->first();
        }
        return $array;
    }

    public function delete($id) {
        $array = ['error' => ''];

        $evaluation = HairdresserEvaluation::find($id);
        if($evaluation) {
            $evaluation->delete();
        } else {
            $array['error'] = 'Não encontrado.';
            return $array;
        }

        return $array;
    }
}
