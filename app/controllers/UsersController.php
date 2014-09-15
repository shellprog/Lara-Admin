<?php

class UsersController extends BaseController {

	public function showCreate()
	{
        $editors = [];
        $datetimepickers = [];

        $columns = AP_Tables::where('table_name',Config::get('admin-config.users_table'))->get();

        foreach($columns as $column){

            if($column->type=="content_editor_escape"||$column->type=="content_editor"){
                $editors[] = $column->column_name;
            }

            if($column->type=="datetime"){
                $datetimepickers[] = $column->column_name;
            }

            if($column->type=="radio"){
                $radios = AP_Keypair::where("ap_table_id",$column->id)->get();
                $column->radios = $radios;
            }

            if($column->type=="checkbox"){
                $checkboxes = AP_Keypair::where("ap_table_id",$column->id)->get();
                $column->checkboxes = $checkboxes;
            }

            if($column->type=="range"){
                $range = AP_Keypair::where("ap_table_id",$column->id)->first();
                $column->range_from = $range->key;
                $column->range_to = $range->value;
            }

            if($column->type=="select"){
                $selects = AP_Keypair::where("ap_table_id",$column->id)->get();
                $column->selects = $selects;
            }
        }

        if(Config::has("admin-config.groups")){
            $groups = DB::table(Config::get("admin-config.groups"))->get();
        }

        return View::make('users.create',['columns'=>$columns,'editors'=>$editors,'datetimepickers'=>$datetimepickers,'groups'=>$groups]);
	}

    public function postCreate(){
        $inputs = Input::except('_token');
        $table_name = Config::get('admin-config.users_table');

        $user = new User();

        foreach($inputs as $column => $value){
            if(Schema::hasColumn($table_name,$column)){
                $user->{$column} = $value;
            }
        }

        $user->save();

        //Create Group if groups exists
        if(Config::has("admin-config.users_groups")){
            if(Schema::hasTable(Config::get("admin-config.users_groups"))){
                if(Input::get("group_id")!=0){
                    $grp['user_id']=$user->id;
                    $grp['group_id']=Input::get("group_id");
                    DB::table(Config::get("admin-config.users_groups"))->insert($grp);
                }
            }
        }

        if(Config::has("admin-config.throttle")){
            if(Schema::hasTable(Config::get("admin-config.throttle"))){

            }
       }
    }
}
