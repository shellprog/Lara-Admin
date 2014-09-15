<?php

class CRUDController extends BaseController
{

    public function index()
    {
        $this->data['rows'] = DB::table("crud_table")->get();
        return View::make('index', $this->data);
    }

    public function create()
    {
        return View::make('crud.create',$this->data);
    }

    public function edit($id)
    {
        $this->data['crud'] = DB::table("crud_table")->where('id',$id)->first();
        return View::make('crud.edit',$this->data);
    }

    public function store()
    {
        $v = Validator::make(['table_name' => Input::get('table_name'),
                'crud_name' => Input::get('crud_name'),
                'creatable' => Input::has('creatable'),
                'editable' => Input::has('editable'),
                'listable' => Input::has('listable')],
            ['crud_name'=>'required','table_name'=>'required|unique:crud_table,table_name', 'creatable'=>'required',
                'editable'=>'required', 'listable'=>'required']);

        if ($v->fails()) {
            Session::flash('error_msg',Utils::buildMessages($v->errors()->all()));
            return Redirect::to("/crud/create")->withErrors($v)->withInput();
        } else {
            DB::table('crud_table')->insert(['crud_name' => Input::get('crud_name'),
                    'table_name' => Input::get('table_name'),
                    'slug' => Str::slug(Input::get('table_name')),
                    'creatable' => Input::has('creatable'),
                    'editable' => Input::has('editable'),
                    'listable' => Input::has('listable'),
                    'created_at'=> Utils::timestamp(),
                    'updated_at'=> Utils::timestamp()]);
        }

        Session::flash('success_msg','CRUD for table '+Input::get('table_name')+' created successfully');

        return Redirect::to("/crud/all")->withErrors($v)->withInput();
    }

    public function delete($id){
        DB::table('crud_table')->where('id',$id)->delete();
        Session::flash('success_msg','CRUD deleted successfully');
        return Redirect::to("/crud/all")->withInput();
    }
}
