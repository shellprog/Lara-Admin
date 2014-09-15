<?php

class SettingsController extends BaseController {

	public function showUserTable()
	{
        $response = ['result'=>0,'message'=>'','columns'=>[]];

        $table = Config::get('admin-config.users_table');

        if(!Schema::hasTable($table)||!Config::has('admin-config.users_table')){
            $response['result']=0;
            $response['message']='Specified table not found';
        }else{
            $response['result']=1;
            $response['message']='';
            $response['table_name']=$table;
            $users_columns = Schema::getColumnListing($table);

            if(DB::table("ap_tables")->where('table_name',$table)->count()<=0){
                foreach($users_columns as $column){
                    $ap_table = new AP_Tables();
                    $ap_table->table_name = $table;
                    $ap_table->type = "text";
                    $ap_table->create_validation_rule = "";
                    $ap_table->edit_validation_rule = "";
                    $ap_table->show_creatable = true;
                    $ap_table->show_editable = true;
                    $ap_table->show_listable = true;
                    $ap_table->column_name = $column;
                    $ap_table->save();
                }
            }

            $columns = AP_Tables::where('table_name',Config::get('admin-config.users_table'))->get();

            foreach($columns as $column){

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

            $response['columns'] = $columns;
        }

       // dd($response['columns'][0]);

        return View::make('settings.users_table',$response);
	}

    public function postUserTable(){

        //delete old columns and populate new ones
        $table_name = Input::get("table_name");

        if(DB::table('ap_tables')->where('table_name',$table_name)->count()>0){
            $cols = DB::table('ap_tables')->where('table_name',$table_name)->get();

            foreach($cols as $col){
                if($col->type=="radio"||$col->type=="range"||$col->type=="checkbox"||$col->type=="select"){
                    DB::table("ap_key_pair")->where("ap_table_id",$col->id)->delete();
                }
            }

            DB::table('ap_tables')->where('table_name',$table_name)->delete();
        }

        $columns = Input::get("columns");

        foreach($columns as $column){
            $ap_table = new AP_Tables();
            $ap_table->table_name = Input::get("table_name");
            $ap_table->type = Input::get($column."_type");
            $ap_table->create_validation_rule = Input::get($column."_create_validator");
            $ap_table->edit_validation_rule = Input::get($column."_edit_validator");
            $ap_table->show_creatable = Input::get($column."_creatable");
            $ap_table->show_editable = Input::get($column."_editable");
            $ap_table->show_listable = Input::get($column."_deletable");
            $ap_table->column_name = $column;
            $ap_table->save();

            if($ap_table->type=="radio"){

                $radionames = Input::get($column."_radioname");
                $radiovalues = Input::get($column."_radioval");

                for($i=0;$i<sizeOf($radionames);$i++){
                    $ap_key_pair = new AP_Keypair();
                    $ap_key_pair->ap_table_id = $ap_table->id;
                    $ap_key_pair->key = $radionames[$i];
                    $ap_key_pair->value = $radiovalues[$i];
                    $ap_key_pair->save();
                }
            }

            if($ap_table->type=="range"){
                $range_from = Input::get($column."_range_from");
                $range_to = Input::get($column."_range_to");

                $ap_key_pair = new AP_Keypair();
                $ap_key_pair->ap_table_id = $ap_table->id;
                $ap_key_pair->key = $range_from;
                $ap_key_pair->value = $range_to;
                $ap_key_pair->save();

            }

            if($ap_table->type=="checkbox"){
                $checkboxnames = Input::get($column."_checkboxname");
                $checkboxvalues = Input::get($column."_checkboxval");

                for($i=0;$i<sizeOf($checkboxnames);$i++){
                    $ap_key_pair = new AP_Keypair();
                    $ap_key_pair->ap_table_id = $ap_table->id;
                    $ap_key_pair->key = $checkboxnames[$i];
                    $ap_key_pair->value = $checkboxvalues[$i];
                    $ap_key_pair->save();
                }
            }

            if($ap_table->type=="select"){
                $selectnames = Input::get($column."_selectname");
                $selectvalues = Input::get($column."_selectval");

                for($i=0;$i<sizeOf($selectnames);$i++){
                    $ap_key_pair = new AP_Keypair();
                    $ap_key_pair->ap_table_id = $ap_table->id;
                    $ap_key_pair->key = $selectnames[$i];
                    $ap_key_pair->value = $selectvalues[$i];
                    $ap_key_pair->save();
                }
            }

        }


        return Redirect::to("/settings/users_table");
    }

    public function findTable($name){

        if(!Schema::hasTable($name)){
            return Response::json(['result'=>0,'message'=>'Specified table not found']);
        }

        return Response::json(['result'=>1,'columns'=>Schema::getColumnListing($name)]);
    }

}
