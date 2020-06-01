<?php

class ApiRouter {
    
    public $params = [];
    
    public function __construct() 
    {
        $this->validateParams();
    }
    
    private function validateParams() 
    {
        
        $route_url = rtrim($_GET['q'], '/');
        $this->params = explode('/', $route_url);
        
        if( !file_exists('classes/' . $this->params[0] . '.class.php') ){
            $this->buildResponse(TRUE, array('description' => 'Method not exist') );
        } else {
            $this->processingRequest();
        }
        
    }
    
    private function processingRequest()
    {
        
        $request = new $this->params[0];
        $function = $this->params[1];
        
        if($request->$function($this->params)){
            $this->buildResponse(FALSE, $request->result );
        } else {
            $this->buildResponse(TRUE, $request->lastError);
        }

    }
    
    private function buildResponse($errors, $data)
    {
        header("HTTP/1.1 400 Bad Request");
        $answer = array_merge(['errors' => $errors], $data);
        
        echo json_encode($answer);
        
    }
    
}