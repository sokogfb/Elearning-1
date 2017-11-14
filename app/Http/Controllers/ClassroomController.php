<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classroom;
use Auth;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::whereHas('members', function($query){
            $query->where('user_id', '=', Auth::id());
        })->get();
        return response()->json($classrooms);
    }

    public function show($classroom_id)
    {
        if(Classroom::find($classroom_id)){
            $classroom = Classroom::findOrFail($classroom_id);

            $posts = $classroom->posts->load('user', 'comments', 'comments.user');
            $assignments = $classroom->assignments->load('user');

            $all = collect();
            foreach ($posts as $post){
                $all->push($post);
            }

            foreach ($assignments as $assignment){
                $all->push($assignment);
            }
            // $posts = $posts->merge($assignments);
            $all = $all->sortByDesc('created_at')->values()->all();

            return response()->json(['classroom' => $classroom, 'posts' => $all]);
        }
        else
        {
            abort(404);
        }
    }

    public function store(Request $request)
    {
        $join_code = str_random(6);

        $classroom = Classroom::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
            'join_code' => $join_code
        ]);

        $classroom->members()->attach(Auth::id());

        return response()->json($classroom);
    }
}
