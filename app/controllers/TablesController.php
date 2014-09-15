<?php

class TablesController extends BaseController
{
    protected $table;

    function __construct()
    {
        $this->beforeFilter('table_settings', array('except' => array('settings')));

        $segments = Request::segments();
        $this->table = DB::table('crud_table')->where('slug', $segments[1])->first();

        parent::__construct();
    }

    public function create()
    {

        $editors = [];
        $datetimepickers = [];

        $columns = DB::table('crud_table_rows')->where('table_name', $this->table->table_name)->get();

        foreach ($columns as $column) {

            if ($column->type == "content_editor_escape" || $column->type == "content_editor") {
                $editors[] = $column->column_name;
            }

            if ($column->type == "datetime") {
                $datetimepickers[] = $column->column_name;
            }

            if ($column->type == "radio") {
                $radios = DB::table("crud_table_pairs")->where("crud_table_id", $column->id)->get();
                $column->radios = $radios;
            }

            if ($column->type == "checkbox") {
                $checkboxes = DB::table("crud_table_pairs")->where("crud_table_id", $column->id)->get();
                $column->checkboxes = $checkboxes;
            }

            if ($column->type == "range") {
                $range = DB::table("crud_table_pairs")->where("crud_table_id", $column->id)->first();
                $column->range_from = $range->key;
                $column->range_to = $range->value;
            }

            if ($column->type == "select") {
                $selects = DB::table("crud_table_pairs")->where("crud_table_id", $column->id)->get();
                $column->selects = $selects;
            }
        }

        $this->data['columns'] = $columns;
        $this->data['editors'] = $editors;
        $this->data['datetimepickers'] = $datetimepickers;
        $this->data['table'] = $this->table;

        return View::make('tables.create', $this->data);
    }

    public function edit($slug, $id)
    {
        $editors = [];
        $datetimepickers = [];

        $columns = DB::table('crud_table_rows')->where('table_name', $this->table->table_name)->get();

        foreach ($columns as $column) {

            if ($column->type == "content_editor_escape" || $column->type == "content_editor") {
                $editors[] = $column->column_name;
            }

            if ($column->type == "datetime") {
                $datetimepickers[] = $column->column_name;
            }

            if ($column->type == "radio") {
                $radios = DB::table("crud_table_pairs")->where("crud_table_id", $column->id)->get();
                $column->radios = $radios;
            }

            if ($column->type == "checkbox") {
                $checkboxes = DB::table("crud_table_pairs")->where("crud_table_id", $column->id)->get();
                $column->checkboxes = $checkboxes;
            }

            if ($column->type == "range") {
                $range = DB::table("crud_table_pairs")->where("crud_table_id", $column->id)->first();
                $column->range_from = $range->key;
                $column->range_to = $range->value;
            }

            if ($column->type == "select") {
                $selects = DB::table("crud_table_pairs")->where("crud_table_id", $column->id)->get();
                $column->selects = $selects;
            }
        }

        $this->data['columns'] = $columns;
        $this->data['editors'] = $editors;
        $this->data['datetimepickers'] = $datetimepickers;
        $this->data['table'] = $this->table;
        $this->data['id'] = $id;

        if ($id != 0) {
            //Table have primary key so fetch using that
            if (DB::table('crud_table_rows')->where('table_name', $this->table->table_name)->count() > 0) {

                $editable_columns_names = DB::table('crud_table_rows')->where('table_name', $this->table->table_name)->where('editable', 1)->lists('column_name');
                $cols = DB::table($this->table->table_name)->select($editable_columns_names)->where('id', $id)->first();

                $this->data['cols'] = (array)$cols;

                return View::make('tables.edit',$this->data);

            }
        } else {
            //Match rows and delete
            $editable_columns_names = DB::table('crud_table_rows')->where('table_name', $this->table->table_name)->lists('column_name');
            $columns = DB::table($this->table->table_name)->select($editable_columns_names)->get();

            if (sizeOf($columns) > 0) {
                $position = Input::get('position');
                $keys = Input::get('rows_' . $position);
                $values = Input::get('values_' . $position);

                $arr = [];

                for ($i = 0; $i < sizeOf($keys); $i++) {
                    $arr[$values[$i]] = $keys[$i];
                }


                $cols = DB::table($this->table->table_name)->where($arr)->first();

                $this->data['cols'] = (array)$cols;

                return View::make('tables.edit', $this->data);

            }

        }
    }

    public function update($slug, $id)
    {

        $inputs = Input::except(['_token', 'rows', 'values']);

        $arr2 = [];

        foreach ($inputs as $column => $value) {
            if (Schema::hasColumn($this->table->table_name, $column)) {
                $arr2[$column] = $value;
            }
        }

        $columns = DB::table('crud_table_rows')->where("table_name",$this->table->table_name)->get();
        $rules = [];
        $data = $inputs;

        for($i=0;$i<sizeOf($columns);$i++){

            if(!empty($columns[$i]->edit_rule)&&isset($data[$columns[$i]->column_name]))
                $rules[$columns[$i]->column_name] = $columns[$i]->edit_rule;
        }

        $v = Validator::make($data,$rules);

        if($v->fails()){
            Session::flash('error_msg',Utils::buildMessages($v->errors()->all()));
            return Redirect::to("/table/".$this->table->slug."/list");
        }

        if ($id != 0) {
            DB::table($this->table->table_name)->where('id', $id)->update($arr2);

            Session::flash('success_msg', 'Entry updated successfully');

            return Redirect::to("/table/{$this->table->slug}/list");
        } else {
            //Match rows and delete
            $editable_columns_names = DB::table('crud_table_rows')->where('table_name', $this->table->table_name)->where('editable', 1)->lists('column_name');
            $columns = DB::table($this->table->table_name)->select($editable_columns_names)->get();

            if (sizeOf($columns) > 0) {
                $position = Input::get('position');
                $keys = Input::get('rows');
                $values = Input::get('values');

                $arr = [];

                for ($i = 0; $i < sizeOf($keys); $i++) {
                    $arr[$keys[$i]] = $values[$i];
                }

                DB::table($this->table->table_name)->where($arr)->update($arr2);

                Session::flash('success_msg', 'Entry updated successfully');

                return Redirect::to("/table/{$this->table->slug}/list");

            }
        }


    }

    public function store()
    {

        $inputs = Input::except('_token');

        $columns = DB::table('crud_table_rows')->where("table_name",$this->table->table_name)->get();
        $rules = [];
        $data = $inputs;

        for($i=0;$i<sizeOf($columns);$i++){

            if(!empty($columns[$i]->create_rule)&&isset($data[$columns[$i]->column_name]))
                $rules[$columns[$i]->column_name] = $columns[$i]->create_rule;
        }

        $v = Validator::make($data,$rules);

        if($v->fails()){
            Session::flash('error_msg',Utils::buildMessages($v->errors()->all()));
            return Redirect::back()->withErrors($v)->withInput();
        }

        $arr = [];

        foreach ($inputs as $column => $value) {
            if (Schema::hasColumn($this->table->table_name, $column)) {
                $arr[$column] = $value;
            }
        }

        DB::table($this->table->table_name)->insert($arr);

        Session::flash('success_msg', 'Entry created successfully');

        return Redirect::to("/table/{$this->table->slug}/list");

    }

    public function all()
    {
        $headers = [];
        $visible_columns_names = DB::table('crud_table_rows')->where('table_name', $this->table->table_name)->where('listable', 1)->lists('column_name');
        $columns = DB::table($this->table->table_name)->select($visible_columns_names)->get();

        if (Schema::hasColumn($this->table->table_name, 'id')) {
            $ids = DB::table($this->table->table_name)->select('id')->lists('id');
        } else {
            foreach ($columns as $column) {
                $ids = 0;
            }
        }

        if (sizeOf($columns) > 0) {
            $headers = array_keys((array)$columns[0]);
        }

        $this->data['headers'] = $headers;
        $this->data['rows'] = Utils::object_to_array($columns);
        $this->data['table'] = $this->table;
        $this->data['ids'] = $ids;

        return View::make('tables.list', $this->data);
    }

    public function settings($table)
    {

        if (!Schema::hasTable($table)) {
            $this->data['result'] = 0;
            $this->data['message'] = 'Specified table not found';
        } else {
            $this->data['result'] = 1;
            $this->data['message'] = '';
            $this->data['table_name'] = $table;
            $users_columns = Schema::getColumnListing($table);

            if (DB::table("crud_table_rows")->where('table_name', $table)->count() <= 0) {
                foreach ($users_columns as $column) {
                    DB::table('crud_table_rows')->insert(
                        ['table_name' => $table,
                            'column_name' => $column,
                            'type' => 'text',
                            'create_rule' => '',
                            'edit_rule' => '',
                            'creatable' => true,
                            'editable' => true,
                            'listable' => true,
                            'created_at' => Utils::timestamp(),
                            'updated_at' => Utils::timestamp()
                        ]);
                }
            }

            $columns = DB::table("crud_table_rows")->where('table_name', $table)->get();

            foreach ($columns as $column) {

                if ($column->type == "radio") {
                    $radios = DB::table("crud_table_pairs")->where("crud_table_id", $column->id)->get();
                    $column->radios = $radios;
                }

                if ($column->type == "checkbox") {
                    $checkboxes = DB::table("crud_table_pairs")->where("crud_table_id", $column->id)->get();
                    $column->checkboxes = $checkboxes;
                }

                if ($column->type == "range") {
                    $range = DB::table("crud_table_pairs")->where("crud_table_id", $column->id)->first();
                    $column->range_from = $range->key;
                    $column->range_to = $range->value;
                }

                if ($column->type == "select") {
                    $selects = DB::table("crud_table_pairs")->where("crud_table_id", $column->id)->get();
                    $column->selects = $selects;
                }
            }

            $this->data['columns'] = $columns;
            $this->data['table'] = $this->table;

        }

        return View::make('tables.settings', $this->data);
    }

    public function postSettings()
    {

        //delete old columns and populate new ones

        if (DB::table('crud_table_rows')->where('table_name', $this->table->table_name)->count() > 0) {

            $cols = DB::table('crud_table_rows')->where('table_name', $this->table->table_name)->get();

            foreach ($cols as $col) {
                if ($col->type == "radio" || $col->type == "range" || $col->type == "checkbox" || $col->type == "select") {
                    DB::table("crud_table_pairs")->where("crud_table_id", $col->id)->delete();
                }
            }

            DB::table('crud_table_rows')->where('table_name', $this->table->table_name)->delete();
        }

        $columns = Input::get("columns");

        foreach ($columns as $column) {

            $insert_id = DB::table('crud_table_rows')->insertGetId(
                ['table_name' => $this->table->table_name,
                    'column_name' => $column,
                    'type' => Input::get($column . "_type"),
                    'create_rule' => Input::get($column . "_create_validator"),
                    'edit_rule' => Input::get($column . "_edit_validator"),
                    'creatable' => Input::has($column . "_creatable"),
                    'editable' => Input::has($column . "_editable"),
                    'listable' => Input::has($column . "_listable"),
                    'created_at' => Utils::timestamp(),
                    'updated_at' => Utils::timestamp()
                ]);

            if (Input::get("type") == "radio") {

                $radionames = Input::get($column . "_radioname");
                $radiovalues = Input::get($column . "_radioval");

                for ($i = 0; $i < sizeOf($radionames); $i++) {
                    DB::table("crud_table_pairs")->insert([
                        'crud_table_id' => $insert_id,
                        'key' => $radionames[$i],
                        'value' => $radiovalues[$i]
                    ]);
                }
            }

            if (Input::get("type") == "range") {
                $range_from = Input::get($column . "_range_from");
                $range_to = Input::get($column . "_range_to");

                DB::table("crud_table_pairs")->insert([
                    'crud_table_id' => $insert_id,
                    'key' => $range_from,
                    'value' => $range_to
                ]);

            }

            if (Input::get("type") == "checkbox") {
                $checkboxnames = Input::get($column . "_checkboxname");
                $checkboxvalues = Input::get($column . "_checkboxval");

                for ($i = 0; $i < sizeOf($checkboxnames); $i++) {

                    DB::table("crud_table_pairs")->insert([
                        'crud_table_id' => $insert_id,
                        'key' => $checkboxnames[$i],
                        'value' => $checkboxvalues[$i]
                    ]);

                }
            }

            if (Input::get("type") == "select") {
                $selectnames = Input::get($column . "_selectname");
                $selectvalues = Input::get($column . "_selectval");

                for ($i = 0; $i < sizeOf($selectnames); $i++) {

                    DB::table("crud_table_pairs")->insert([
                        'crud_table_id' => $insert_id,
                        'key' => $selectnames[$i],
                        'value' => $selectvalues[$i]
                    ]);
                }
            }

        }

        Session::flash('success_msg', 'Table metadata has been updated');

        return Redirect::to("/table/{$this->table->slug}/list");
    }

    public function delete($table_name, $id)
    {
        if ($id != 0) {
            //Table have primary key so delete using that
            if (DB::table('crud_table_rows')->where('table_name', $this->table->table_name)->count() > 0) {
                $cols = DB::table('crud_table_rows')->where('table_name', $this->table->table_name)->get();

                foreach ($cols as $col) {
                    if ($col->type == "radio" || $col->type == "range" || $col->type == "checkbox" || $col->type == "select") {
                        DB::table("crud_table_pairs")->where("crud_table_id", $col->id)->delete();
                    }
                }

                DB::table('crud_table_rows')->where('table_name', $this->table->table_name)->delete();

                DB::table($this->table->table_name)->where('id', $id)->delete();

                Session::flash('success_msg', 'Entry deleted successfully');

                return Redirect::to("/table/{$this->table->slug}/list");

            }
        } else {
            //Match rows and delete
            $visible_columns_names = DB::table('crud_table_rows')->where('table_name', $this->table->table_name)->lists('column_name');
            $columns = DB::table($this->table->table_name)->select($visible_columns_names)->get();

            if (sizeOf($columns) > 0) {
                $position = Input::get('position');
                $keys = Input::get('rows_' . $position);
                $values = Input::get('values_' . $position);

                $arr = [];

                for ($i = 0; $i < sizeOf($keys); $i++) {
                    $arr[$values[$i]] = $keys[$i];
                }

                DB::table($this->table->table_name)->where($arr)->delete();

                Session::flash('success_msg', 'Entry deleted successfully');

                return Redirect::to("/table/{$this->table->slug}/list");

            }

        }
    }

}
