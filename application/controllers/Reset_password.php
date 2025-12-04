<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reset_password extends MY_Controller
{
    public function __construct(){
        parent::__construct();
        $this->load->library(['form_validation']);
        $this->load->model('Auth_model', 'auth');
    }

    public function index()
    {
        $this->load->view('users/forgot_password');
    }

    public function reset()
    {
        $this->form_validation->set_rules('username', $this->lang->line('change_password_validation_old_password_label'), 'required');
        $this->form_validation->set_rules('password', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[5]|matches[confirm_password]');
        $this->form_validation->set_rules('confirm_password', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');

        if ($this->form_validation->run() == FALSE){
            $data = [
                'status' => false,
                'errors' => [
                    'username' => form_error('username'),
                    'password' => form_error('password'),
                    'confirm_password' => form_error('confirm_password')
                ]
            ];
        } else {
            $username 	= $this->input->post('username', true);
            $pass 	= $this->input->post('password', true);
            $user = $this->auth->check_level($username);
            if ($user) {
                $change = $this->db->update('users', ['password' => password_hash($pass, PASSWORD_BCRYPT)], ['username' => $username]);
                if($change){
                    if ($user->group_id == '1') {
                        $status = $this->db->update('users', ['password' => password_hash($pass, PASSWORD_BCRYPT)], ['username' => $username]);
                    } elseif ($user->group_id == '2') {
                        $this->db->update('master_guru', ['password' => $pass], ['username' => $username]);
                        $status = $this->db->update('users', ['password' => password_hash($pass, PASSWORD_BCRYPT)], ['username' => $username]);
                    } elseif ($user->group_id == '3') {
                        $this->db->update('master_siswa', ['password' => $pass], ['username' => $username]);
                        $status = $this->db->update('users', ['password' => password_hash($pass, PASSWORD_BCRYPT)], ['username' => $username]);
                    } else {
                        $status = false;
                    }
                    $data['status'] = $status;
                    $data['message'] = $status ? '' : 'Username tidak dikenal';
                } else{
                    $data = [
                        'status' 	=> false,
                        'message'	=> 'Reset password gagal!'
                    ];
                }
            } else {
                $data = [
                    'status' 	=> false,
                    'message'	=> 'Username tidak terdaftar!'
                ];
            }
        }
        $this->output_json($data);
    }
}