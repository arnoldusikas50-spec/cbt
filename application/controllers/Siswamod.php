<?php 


class Siswamod extends MY_Controller
{

public function __construct()
{
    parent::__construct();
    if (!$this->auth->logged_in()) {
        redirect('auth');
    }
    $this->load->library('upload');
    $this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
    $this->load->library('user_agent');
    $this->load->model('Cbt_model', 'cbt');  // Add this line to load the Cbt_model
    $this->form_validation->set_error_delimiters('', '');
}

    public function output_json($data, $encode = true)
    {
        if ($encode) $data = json_encode($data);
        $this->output->set_content_type('application/json')->set_output($data);
    }

    public function index()
    {
    }


public function cekTokenmod() {
    try {
        // Check if the model is loaded
        if (!isset($this->cbt)) {
            throw new Exception('Cbt_model not loaded');
        }

        $token = $this->cbt->getToken();
        
        // Log the token for debugging
        log_message('debug', 'Token data: ' . print_r($token, true));
        
        if ($token === null) {
            $response = ['token' => 'ABCDEF', 'auto' => '0'];
        } else {
            $response = ['token' => $token->token ?? 'ABCDEF'];
        }
        
        $this->output_json($response);
    } catch (Exception $e) {
        // Log the error
        log_message('error', 'Error in cekTokenmod: ' . $e->getMessage());
        
        // Return a default response in case of error
        $this->output->set_status_header(500);
        $this->output_json(['error' => 'Internal server error', 'message' => $e->getMessage()]);
    }
}

}
