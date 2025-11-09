<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Language extends Home_Controller {

    public function __construct()
    {
        parent::__construct();
        //check auth
        if (!is_admin() && !is_user()) {
            redirect(base_url());
        }
        $this->load->dbforge();
    }


    public function index()
    {
        $data = array();
        $data['page_title'] = 'Language';   
        $data['language'] = FALSE;
        $data['languages'] = $this->admin_model->get_language();
        $data['main_content'] = $this->load->view('admin/language/language',$data,TRUE);
        $this->load->view('admin/index',$data);
    }


    // add language
    public function add()
    {   
        if($_POST)
        {   
            check_status();

            if (!is_writable('application/language')):
                $this->session->set_flashdata('error', 'script > application > "language" folder is not writable, please writable this folder.');
                redirect(base_url('admin/language')); exit();
            endif;

            $lang_name = $this->input->post('name', true);
            if (strlen($lang_name) != strlen(utf8_decode($lang_name)))
            {
                $this->session->set_flashdata('error', 'Language name must be english characters');
                redirect(base_url('admin/language')); exit();
            }


            $id = $this->input->post('id', true);
            if ($id == '') {
                $is_unique = '|is_unique[language.name]';
            }else{
                $is_unique = '';
            }

            $this->form_validation->set_rules('name', trans('name'), 'required'.$is_unique);

            if ($this->form_validation->run() === false) {
                $this->session->set_flashdata('error', validation_errors());
                redirect(base_url('admin/language'));
            } else {
               
                $data=array(
                    'name' => $this->input->post('name', true),
                    'slug' => str_slug($this->input->post('name', true)),
                    'short_name' => $this->input->post('short_name', true),
                    'text_direction' => $this->input->post('text_direction', true),
                    'status' => 1
                );
                $data = $this->security->xss_clean($data);

                if ($id != '') {
                    $this->admin_model->edit_option($data, $id, 'language');
                    $this->session->set_flashdata('msg', trans('updated-successfully')); 

                    $lang = str_slug($_POST['lang_name']);
                    
                  
                    if (is_dir(APPPATH.'language/'.$lang)) {
                        rename(APPPATH.'language/'.$lang, APPPATH.'language/'.str_slug($this->input->post('name', true)));
                    }

                    $fields = array(
                        $lang => array(
                            'name' => str_slug($this->input->post('name', true)),
                            'type' => 'TEXT',
                            'constraint' => '255'
                        ),
                    );
                    $this->dbforge->modify_column('lang_values', $fields);

                } else {
                    $this->admin_model->insert($data, 'language');
                    $this->session->set_flashdata('msg', trans('inserted-successfully')); 
                
                   
                    $fields = array(
                        str_slug($_POST['name']) => array('type' => 'TEXT', 'after' => 'english')
                    );
                    $this->dbforge->add_column('lang_values', $fields);

                    
                    $dir = str_slug($_POST['name']);
                    if (!is_dir(APPPATH.'language/'.$dir)) {
                        mkdir(APPPATH.'./language/' . $dir, 0777, TRUE);
                        copy(APPPATH.'language/english/website_lang.php', APPPATH.'language/'.$dir.'/website_lang.php');
                    }
                }

                redirect(base_url('admin/language'));

            }
            
        }     
    }


    
    public function add_value()
    {   
        check_status();

        if($_POST){

            $check = $this->admin_model->check_keyword(str_slug($this->input->post('keyword', true)));
            if ($check == 1) {
                $this->session->set_flashdata('error', 'keyword-exists');
                redirect($_SERVER['HTTP_REFERER']);
            } else {

                $data=array(
                    'label' => $this->input->post('label', true),
                    'keyword' => character_limiter(str_slug($this->input->post('keyword', true)), 2),
                    'english' => $this->input->post('label', true),
                    'type' => $this->input->post('type', true)
                );
                $data = $this->security->xss_clean($data);
                $this->admin_model->insert($data, 'lang_values');
                $this->session->set_flashdata('msg', trans('inserted-successfully')); 
                redirect(base_url('admin/language/values/'.$this->input->post('type').'/'.$this->input->post('lang')));
            }
        }
    }


    public function test()
    {

        //exit();
        // $values = $this->admin_model->select_asc('lang_values');
        // foreach ($values as $value) {
        //     echo  "'".html_escape($value->id)."' => '".html_escape($value->english)."',<br>";
        // }
        // exit();

        $variable = array(
           '1474' => 'פרטי הפעלה',
'1475' => 'צור חשבון חדש',
'1476' => 'שם משתמש',
'1477' => 'הפעלה',
'1478' => 'סקירה כללית',
'1479' => 'רקע',
'1480' => 'סטטיסטיקות קהילה',
'1481' => 'הפעלות מורכבות',
'1482' => 'זמן חונכות כולל',
'1483' => 'נוכחות ממוצעת',
'1484' => 'סשן ספר',
'1485' => 'מזהה הזמנה',
'1486' => 'סטטוס הזמנה',
'1487' => 'מידע על חונכות',
'1488' => 'מנטור',
'1489' => 'הנחה חדשה',
'1490' => 'פעם אחת לכל חונך',
'1491' => 'ללא תשלום',
'1492' => 'החלת קופון',
'1493' => 'הזמנות',
'1494' => 'זמן הזמנה',
'1495' => 'הזמנת מפגשים',
'1496' => 'סנכרן את Google Calednder',
'1497' => 'בחר קטגוריה',
'1498' => 'חנכות',
'1499' => 'בחר את החוויה שלך',
'1500' => 'כשתהיה זמין',
'1501' => 'הגדר את זמינותך להפעלה זו. תקבל הזמנות באזור הזמן המקומי שלך',
'1502' => 'שעות אספקה',
'1503' => 'הגדר שעות מותאמות אישית',
'1504' => 'קופון משומש',
'1505' => 'זיהוי חשבון זום',
'1506' => 'מזהה לקוח זום',
'1507' => 'סוד לקוח זום',
'1508' => 'Zoom API',
'1509' => 'מסמך שילוב זום',
'1510' => 'צור אפליקציית זום',
'1511' => 'בדוק חיבור API',
'1512' => 'שלח דואר התראה למשתמש על הצטרפות לפגישה',
'1513' => 'התחל פגישה',
'1514' => 'בטל פגישה',
'1515' => 'צור פגישה',
'1516' => 'הצטרף לפגישה',
'1517' => 'מארח פגישה',
'1518' => 'סיסמת פגישה',
'1519' => 'פגישה מקוונת',
'1520' => 'אפשרות ברירת מחדל לפגישה וירטואלית',
'1521' => 'כתובת אתר להזמנה של Google Meet',
'1522' => 'עדיין לא התחיל',
'1523' => 'השב ב',
'1524' => 'ההזמנות האחרונות',
'1525' => 'בחר הפעלה',
'1526' => 'בחר חונך',
'1527' => 'בחר סטטוס',
'1528' => 'בחר מנטור',
'1529' => 'ראה הכל',
'1530' => 'הזמנות קרובות',
'1531' => 'מנטורים',
'1532' => 'מפגשים שהושלמו',
'1533' => 'שנים',
'1534' => 'נוכחות',
'1535' => 'דווח',
'1536' => 'סה"כ הפעלות',
'1537' => 'רמת חונכות',
'1538' => 'מפגשי מנטור',
'1539' => 'סה"כ הזמנה',
'1540' => 'מדינות',
'1541' => 'מידע על רווחים',
'1542' => 'סה"כ חונכות',
'1543' => 'מידע חוזר',
'1544' => 'בקרוב',
'1545' => 'קבלת תשלום',
'1546' => 'המפגשים המוזמנים ביותר',
'1547' => 'חניך המוזמן ביותר',
'1548' => 'המדינה המוזמנת ביותר',
'1549' => 'אסימון גישה',
'1550' => 'שוטף בשטף',
'1551' => 'מועדף',
'1552' => 'מנטורים מועדפים',
'1553' => 'חניכים מועדפים',
'1554' => 'אודותינו',
'1555' => 'לקוחות מרוצים',
'1556' => 'מותגים',
'1557' => 'גופנים',
'1558' => 'מותג',
'1559' => 'לוגו',
'1560' => 'שם גופן',
'1561' => 'גוגל גופנים',
'1562' => 'גופן מותאם אישית',
'1563' => 'נהל גופנים',
'1564' => 'הודעה אל',
'1565' => 'שלח הודעה',
'1566' => 'בחר את המדינה שלך',
'1567' => 'בחר את אזור הזמן שלך',
'1568' => 'Ultramsg API',
'1569' => 'מזהה מופע',
'1570' => 'אסימון',
'1571' => 'אפשר הודעת אישור הזמנה Ultra',
'1572' => 'אפשר לשלוח הודעת אולטרה הזמנה ללקוחות שלך, לאחר קביעת פגישה.',
'1573' => 'הפעל SMS אישור הזמנה',
'1574' => 'אפשר לשלוח הודעת הודעת הזמנה ללקוחות שלך, לאחר קביעת פגישה.',
'1575' => 'הגדרות וואטסאפ',
'1576' => 'התייעצות',
'1577' => 'המפגש הזה חוזר על עצמו',
'1578' => 'יש להפעלה זו סך הכל',
'1579' => 'זמינות',
'1580' => 'עבור לקופה',
'1581' => 'הפרק הבא שלך, התאפשר בזכות הדרכה',
'1582' => 'בנה ביטחון עצמי כמנהיג, הגדל את הרשת שלך והגדר את המורשת שלך.',
'1583' => 'מנטור כותרת הבקשה',
'1584' => 'הפך לחבר',
'1585' => 'למד וצמיח על פני מומחיות בחינם',
'1586' => 'מצא מנטורים מתחומי מוצר ברחבי העולם',
'1587' => 'הצוותים שלנו',
'1588' => 'גלה את המנטורים המובילים בעולם',
'1589' => 'בדרך כלל מגיב ב',
'1590' => 'נסיונות',
'1591' => 'שדרוג תוכנית',
'1592' => 'הגעת למגבלה המקסימלית',
'1593' => 'שדרג את התוכנית שלך',
'1594' => 'דוא"ל של Paypal',
'1595' => 'התראות',
'1596' => 'המפגש הבא',
'1597' => 'הפעלה חוזרת הושלמה',
'1598' => 'השלם תשלום',
'1599' => 'סה"כ דקות',
'1600' => 'חניכים',
'1601' => 'ימי נכים',
'1602' => 'מפגשים ממתינים',
'1603' => 'חזר על עצמו',
'1604' => 'סה"כ הפעלה',
'1605' => 'ספירה חוזרת',
'1606' => 'הוחל השובר',
'1607' => 'יומי',
'1608' => 'שבועי',
'1609' => 'מספר הפגישה',
'1610' => 'בחר את המין שלך',
        );
        
        //echo "<pre>"; print_r($variable); exit();
        foreach ($variable as $key => $value) {
            
            $vdata=array(
                'hebrew' => $value
            );
            //$this->admin_model->update($vdata, $key, 'lang_values');
        }
        echo "done";
        exit();

    }


    //show language values
    public function values($type, $slug)
    {   
        $data = array();  
        $data['page_title'] = 'language';  
        $data['value'] = $slug;  
        $data['type'] = $type;  
        $data['language'] = $this->admin_model->get_lang_values_by_type($type);
        $data['main_content'] = $this->load->view('admin/language/language_values',$data,TRUE);
        $this->load->view('admin/index',$data);
    }

    //update language values
    public function update_values($type)
    {   
        check_status();

        $data = array();
        $language = $this->input->post('lang_type', true);
        $languages = $this->admin_model->get_lang_values_by_type($type);
        

        ini_set('memory_limit', '-1');
        set_time_limit ( -1);

        foreach ($languages as $lang) {
            $value = 'value'.$lang->id;

            $data=array(
                $_POST['lang_type'] => $_POST[$value]
            );
            $this->admin_model->edit_option($data, $lang->id, 'lang_values');
        }
        $this->session->set_flashdata('msg', trans('updated-successfully'));
        redirect(base_url('admin/language/values/'.$type.'/'.$language));

    }


    //edit language values
    public function edit($id)
    {  
        $data = array();
        $data['page_title'] = 'Edit';   
        $data['language'] = $this->admin_model->select_option($id, 'language');
        $data['main_content'] = $this->load->view('admin/language/language',$data,TRUE);
        $this->load->view('admin/index',$data);
    }

    //active language    
    public function active($id) 
    {
        $data = array(
            'status' => 1
        );
        $data = $this->security->xss_clean($data);
        $this->admin_model->update($data, $id,'language');
        $this->session->set_flashdata('msg', trans('msg-activated')); 
        redirect(base_url('admin/language'));
    }


    //deactive language
    public function deactive($id) 
    {
        check_status();
        
        $language = $this->admin_model->get_by_id($id,'language');
        $data = array(
            'status' => 0
        );
        $data = $this->security->xss_clean($data);
        if ($language->name != 'english') {
            $this->admin_model->update($data, $id,'language');
            $this->session->set_flashdata('msg', trans('msg-deactivated')); 
        }
        redirect(base_url('admin/language'));
    }


    //delete language
    public function delete($id)
    {
        check_status();

        $language = $this->admin_model->get_by_id($id,'language');
     
        $lang = $language->slug;
        if ($lang != 'english') {

            //delete language folder & file
            if (is_dir(APPPATH.'language/'.$lang)) {
                unlink(APPPATH.'language/'.$lang.'/website_lang.php');
                rmdir(APPPATH.'language/'.$lang);
            }

            //delete database column 
            $this->dbforge->drop_column('lang_values', $lang);
            $this->admin_model->delete($id,'language'); 
        }
        echo json_encode(array('st' => 1));
    }

}
    

