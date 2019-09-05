<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Operation;
use App\Task;
use App\Month;
use App\Programming;
use App\OperationProgramming;
use Illuminate\Support\Facades\DB;
class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        // return $request->all();
        $programaciones = json_decode($request->programacion);
        // dd($programaciones) ;
        $programmings=[];
        foreach($programaciones as $programacion){
            // $programming = new Programming;
            // $programming->task_id = $task->id;
            // $programming->month_id = $programacion->id;
            // $programming->meta = $programacion->meta;
            // $programming->save();

            $programmings+=array(''.$programacion->id => ['meta' => $programacion->meta]);

        }
        // dd($programmings);
        if($request->task_id!=''){
            $task = Task::find($request->task_id);
        }else{
            $task = new Task;
        }
        $task->operation_id =$request->operation_id;
        $task->description = $request->description;
        $task->meta = $request->meta;
        $task->weighing = $request->weighing;
        $task->code = $request->code;
        if($request->its_contribution =='true')
        {
            $task->its_contribution = true;
        }else {
            $task->its_contribution = false;
        }
        $task->save();
        // $task->code = 'T-'.$task->id;
        // $task->save();
        $task->programmings()->sync($programmings);


         //actualizacion de numeracion
         $tasks= Task::where('id','>',$task->id)->orderBy('id')->get();
         $num = $task->code ;
         foreach($tasks as  $task){
             $num++;
             $task->code = $num;
             $task->save();
         }

        session()->flash('message','se registro '.$task->code);
        return back()->withInput();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $task = Task::with('programmings')->find($id);
        return response()->json(compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function operation_tasks($operation_id)
    {
        $operation = Operation::with('operation_programmings')->find($operation_id);
        $title = "Tareas de ".$operation->code;
        $meses = Month::all();
        return view('task.index',compact('operation','title','meses'));
    }
    public function check_meta($operation_id){


        $total_meta = Task::where('operation_id',$operation_id)
                                    ->select(DB::raw("sum(meta) as total_meta"))
                                    ->where('its_contribution','=',true)
                                    ->groupBy('operation_id')
                                    ->get();

        $total_contribution = Task::where('operation_id',$operation_id)
                                ->select(DB::raw("sum(weighing) as total_ponderado"))
                                ->groupBy('operation_id')
                                ->get();

        $operation = Operation::find($operation_id);
        if(sizeof($total_meta)>0)
        {
            $meta = $operation->meta - $total_meta[0]->total_meta;
        }
        else{

            $meta = $operation->meta;
        }
        if(sizeof($total_contribution)>0)
        {
            $ponderacion = 100 - $total_contribution[0]->total_ponderado;
        }else{
            $ponderacion = 100;
        }
        $programmings = OperationProgramming::where('operation_id',$operation->id)->get();

        // $programmings = Programming::with('month')->where('task_id',$task->id)->get();
        // $specific_programmings= [];
        $operation_programmings=[];
        // foreach($programmings as $programming)
        // {
        //     // $specific_task_programmations = SpecificTaskProgrammation::with('programming')
        //     //                                                         ->where('programming_id',$programming->id)
        //     //                                                         ->get();
        //     $operation_programmations = Programming::where('')->get();
        //     $sum_meta = 0;

        //     foreach($specific_task_programmations as $specific_task_programmation )
        //     {
        //         $sum_meta+= $specific_task_programmation->meta;
        //         // array_push();
        //     }
        //     $programming->meta -= $sum_meta;
        //     array_push($specific_programmings,$programming);
        // }

        return response()->json(compact('meta','ponderacion'));

    }
}
