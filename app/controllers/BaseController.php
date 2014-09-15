<?php

class BaseController extends Controller {

    protected $cruds;
    protected $data;

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

    function __construct(){
        $this->data['cruds'] = DB::table('crud_table')->get();
    }

}
