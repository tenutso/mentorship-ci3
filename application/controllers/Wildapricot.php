<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class WAOauth extends GenericProvider
{
    public function __construct($options = [])
    {
        parent::__construct($options);
    }

    protected function getAccessTokenRequest(array $params)
    {
        $request = parent::getAccessTokenRequest($params);
        //print_r($params);
        return $request->withHeader('Authorization', 'Basic ' . base64_encode(
            $params['client_id'] . ':' . $params['client_secret']
        ));
    }
}


class Wildapricot extends Home_Controller
{

    public function __construct()
    {
        parent::__construct();

    }

    public function oauth()
    {
        $provider = new WAOauth([
            'clientId' => settings()->wildapricot_client_id,    // The client ID assigned to you by the provider
            'clientSecret' => settings()->wildapricot_client_secret,    // The client password assigned to you by the provider
            'redirectUri' => base_url('wa/auth/oauth'),
            'urlAuthorize' => 'https://' . settings()->wildapricot_domain . '/sys/login/OAuthLogin',
            'urlAccessToken' => 'https://oauth.wildapricot.org/auth/token',
            'urlResourceOwnerDetails' => 'https://api.wildapricot.org/v2.1/accounts/' . settings()->wildapricot_account_id . '/contacts/me',
            'scopes' => ['contacts_me']
        ]);

        if (!isset($_GET['code'])) {

            // Fetch the authorization URL from the provider; this returns the
            // urlAuthorize option and generates and applies any necessary parameters
            // (e.g. state).

            $authorizationUrl = $provider->getAuthorizationUrl();
            //print_r($authorizationUrl);

            // Get the state generated for you and store it to the session.
            $this->session->set_userdata('oauth2state', $provider->getState());

            // Optional, only required when PKCE is enabled.
            // Get the PKCE code generated for you and store it to the session.
            $this->session->set_userdata('oauth2pkceCode', $provider->getPkceCode());

            // Redirect the user to the authorization URL.
            header('Location: ' . $authorizationUrl);
            exit;

            // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || empty($this->session->oauth2state) || $_GET['state'] !== $this->session->oauth2state) {

            if (isset($this->session->oauth2state)) {
                $this->session->unset_userdata('oauth2state');
            }

            exit('Invalid state');

        } else {
            //echo "New State: " . $_GET['state'] . "<br>";
            //echo "Stored State: " . $this->session->oauth2state . "<br>";
            //echo "Code: " . $_GET['code'] . '<br>';
            //print_r($provider);

            try {
                // Optional, only required when PKCE is enabled.
                // Restore the PKCE code stored in the session.

                $provider->setPkceCode($this->session->oauth2pkceCode);


                $options['code'] = $_GET['code'];
                // Try to get an access token using the authorization code grant.
                $tokens = $provider->getAccessToken('authorization_code', $options);
                $resourceOwner = $provider->getResourceOwner($tokens);


                $profile = $resourceOwner->toArray();


                $user = $this->oauth_model->validate_user($profile['Email']);


                if (empty($user)) { // go to create account
                    //$user = $this->createUser($profile);
                    $this->session->set_userdata('profile', $profile);
                    //$this->register($profile);
                    redirect('wa/auth/register', 'refresh');
                } else if (!empty($user) && $user->status == 2) {
                    // account suspend
                    echo "Account suspended";

                } else
                    $this->doLogin($user);


            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                echo "OAuth2 error: " . $e->getMessage() . PHP_EOL;
                echo "Response body: " . $e->getResponseBody() . PHP_EOL;
            } catch (\Throwable $e) {
                echo "General error: " . $e->getMessage() . PHP_EOL;
            }
        }

    }

    private function doLogin($user)
    {
        $data = array(
            'id' => $user->id,
            'name' => $user->name,
            'slug' => $user->slug,
            'thumb' => $user->thumb,
            'email' => $user->email,
            'role' => $user->role,
            'parent' => 0,
            'logged_in' => TRUE,
        );
        $data = $this->security->xss_clean($data);
        $this->session->set_userdata($data);

        $data = array(
            'is_active' => 1,
            'last_active' => my_date_now(),
        );
        $data = $this->security->xss_clean($data);
        $this->admin_model->edit_option($data, user()->id, 'users');


        // success notification
        if ($user->role == 'admin') {
            $url = base_url('admin/dashboard');
        } else if ($user->role == 'user') {
            $url = base_url('admin/dashboard/user');
        } else if ($user->role == 'customer') {
            $url = base_url('customer/orders');
        } else {
            $url = base_url('admin/dashboard/mentee');
        }

        // echo "USER DETAILS: <pre>";
        // print_r($user);
        // echo "</pre>";

        $this->session->unset_userdata('oauth2pkceCode');
        $this->session->unset_userdata('profile');

        header('location:' . $url);
    }

    public function register()
    {
        $profile = $this->session->userdata('profile');
        if (empty($profile)) {
            exit("Authentication profile missing. Please login again.");
        }
        $data = array();
        $data['page_title'] = 'Register';
        $data['page'] = 'Auth';
        if (settings()->enable_frontend == 1) {
            $data['menu'] = TRUE;
        } else {
            $data['menu'] = FALSE;
        }
        $data['countries'] = $this->admin_model->select_asc('country');
        $data['time_zones'] = $this->admin_model->select_asc('time_zone');
        $data['categories'] = $this->admin_model->get_site_categories('categories');
        $data['dialing_codes'] = $this->common_model->select_asc('dialing_codes');
        $data['profile'] = $this->session->userdata('profile');
        $data['main_content'] = $this->load->view('register_oauth', $data, TRUE);
        $this->load->view('index', $data);
    }

    public function complete_registration()
    {
        if (!$_POST) {
            echo "this url is available to post only";
            exit("No direct script access allowed");
        }

        $profile = $this->session->userdata('profile');

        $name = $profile['FirstName'] . ' ' . $profile['FirstName'];
        $check_slug = check_mentor_slug(str_slug($name));
        $random_slug_code = random_string('numeric', 3);
        if ($check_slug == 1) {
            $slug = str_slug($name) . '-' . $random_slug_code;
        } else {
            $slug = str_slug($name);
        }

        if ($this->input->post('register_type') == 1) {
            $role = 'user';
        }
        if ($this->input->post('register_type') == 2) {
            $role = 'mentee';
        }

        if (settings()->enable_mentor_auto_approve == 1) {
            $status = 1;
        } else {
            $status = 0;
        }

        $user_type = 'registered';
        $trial_expire = date('Y-m-d');


        $code = random_string('numeric', 4);
        $data = array(
            'name' => $name,
            'slug' => $slug,
            'user_name' => str_slug($name),
            'email' => $profile['Email'],
            'phone' => '0',
            'password' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
            'role' => $role,
            'user_type' => $user_type,
            'trial_expire' => $trial_expire,
            'status' => $status,
            'parent_id' => 0,
            'verify_code' => $code,
            'email_verified' => 1,
            'enable_appointment' => 0,
            'category' => $this->input->post('category', true),
            'language' => $this->input->post('language', true),
            'country' => $this->input->post('country', true),
            'time_zone' => $this->input->post('time_zone', true),
            'intervals' => 30,
            'image' => 'assets/images/no-photo-sm.png',
            'thumb' => 'assets/images/no-photo-sm.png',
            'created_at' => my_date_now()
        );
        $data = $this->security->xss_clean($data);
        $id = $this->common_model->insert($data, 'users');


        $skills = $this->input->post('skills');
        foreach ($skills as $skill) {
            $data = array(
                'user_id' => $id,
                'skill_id' => $skill,
            );

            $data = $this->security->xss_clean($data);
            $this->admin_model->insert($data, 'users_skill');
        }

        $user = $this->auth_model->validate_id(md5($id));

        $data = array(
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'thumb' => $user->thumb,
            'email' => $user->email,
            'logged_in' => true
        );
        $this->session->set_userdata($data);

        // insert notification
        $notify = array(
            'user_id' => $id,
            'action_id' => 0,
            'content_id' => 0,
            'text' => trans('welcome-to') . ' ' . settings()->site_name,
            'noti_type' => 1,
            'noti_time' => my_date_now()
        );
        $notify = $this->security->xss_clean($notify);
        $this->common_model->insert($notify, 'notifications');

        $this->session->unset_userdata('oauth2pkceCode');
        $this->session->unset_userdata('profile');


        if ($role == 'user') {
            $url = base_url('admin/dashboard/user');
        } else {
            $url = base_url('admin/dashboard/mentee');
        }

        echo json_encode(array('st' => 1, 'url' => $url));
        exit();
    }
}