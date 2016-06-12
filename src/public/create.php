<?php
/**
*    @file    \applications\employees\index.php
*    @date    04/02/16
*    @class   indexController
*/
namespace Atmosphere\Controller
{
    class create extends Controller
    {
        /**
        *
        */
        public function _config()
        {
            $this->import(array(
                'Employees',
            ));
            
        }
        /**
        *
        */
        public function index()
        {
            if (
                array_key_exists('firstname', $_POST)
                AND array_key_exists('lastname', $_POST)
                AND array_key_exists('email', $_POST)
                AND array_key_exists('tin', $_POST)
                AND array_key_exists('gender', $_POST)
                AND array_key_exists('birthday', $_POST)
            ) {
                $this->Employees->_enable();
                $token = $this->Employees->create($_POST);
                if (!empty($token)) {
                    $token = $this->Employees->getUUIDFromId($token);
                    header('Location:/employees/view/'.$token, true);
                    exit;
                }
            } else {
                
            }
            $this->template->load('employees/create.tpl');
        }
        
        public function load ($imported, $total)
        {
            if (!is_numeric($imported)) {
                $imported = null;
            }
            if (!is_numeric($total)) {
                $total = null;
            }
            if (array_key_exists('file', $_FILES)) {
                $this->Employees->_enable();
                $file = new \Atmosphere\Upload\File;
                $file->setInput('file');
                $file->setDestinationDirectory(\Env::get('filesystem.tmp'));
                $file->setAllowedMimeTypes(array(
                    'application/vnd.ms-excel',
                    'application/msexcel',
                    'application/x-msexcel',
                    'application/x-ms-excel',
                    'application/x-excel',
                    'application/x-dos_ms_excel',
                    'application/xls',
                    'application/x-xls',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.google-apps.spreadsheet',
                    'application/vnd.oasis.opendocument.text',
                    'application/vnd.oasis.opendocument.spreadsheet',
                    'text/plain',
                    'text/csv',
                    'application/csv',
                    'text/comma-separated-values',
                    // not secure
                    'application/octet-stream',
                ));
                $file->setAutoFilename();
                $file->save();
                \Debug::log($file->getInfo());
                include_once(\Env::get('filesystem.libraries').'/PHPExcel/PHPExcel.php');
                include_once(\Env::get('filesystem.libraries').'/PHPExcel/PHPExcel/Autoloader.php');
                $objPHPExcel = \PHPExcel_IOFactory::load($file->getInfo()->destination);
                $rows = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
                \Debug::log($rows);
                $rowsNotInserted = [];
                foreach ($rows AS $row) {
                    $$messages = [];
                    $insertIsValid = true;
                    $row = array_values($row);
                    if (empty($row[0]) OR empty($row[1])) {
                        // firstname or lastname s invalid
                        $messages[] = 'first n lastname';
                        $insertIsValid = false;
                    }
                    
                    if (preg_match('#^\d{1,2}\/\d{1,2}\/\d{4}$#', $row[2])) {
                        $row[2] = date('Y-m-d', strtotime($row[2]));
                        
                    } else {
                        //
                        $messages[] = 'Birthday';
                        $insertIsValid = false;
                    }
                    
                    $genders = array('hombre', 'mujer', 'varon', 'dama', 'male', 'female', 'h', 'm');
                    if (!in_array(strtolower($row[3]), $genders)) {
                        //
                        $messages[] = 'gender';
                        $insertIsValid = false;
                    } else {
                        $row[3] = str_replace(
                                array('hombre', 'mujer', 'varon', 'dama', 'male', 'female', 'h', 'm'),
                                array('MALE', 'FEMALE', 'MALE', 'FEMALE', 'MALE', 'FEMALE', 'MALE', 'FEMALE'),
                                strtolower($row[3])
                        );
                    }
                    if (!filter_var($row[4], FILTER_VALIDATE_EMAIL)) {
                        //
                        $messages[] = 'email';
                        $insertIsValid = false;
                    }
                    
                    if (empty($row[5])) {
                        //
                        $messages[] = 'TIN';
                    }
                    
                    if (empty($row[6]) OR !preg_match('#^\+?\d{1,2}?\d{1,2}\d{3,4}\s+?\d{3,4}$#', $row[6])) {
                        //   
                        $messages[] = 'phone';
                    }
                    
                    if ($insertIsValid === false) {
                        $rowsNotInserted[] = array(
                            'messages'  =>  $messages,
                            'rows'      =>  $row
                        );
                        continue;
                    }
                    
                    $data = array(
                        'firstname'     =>  $row[0],
                        'lastname'      =>  $row[1],
                        'birthday'      =>  $row[2],
                        'gender'        =>  $row[3],
                        'email'         =>  $row[4],
                        'tin'           =>  $row[5],
                        'mobile'        =>  $row[6],
                    );
                    $employee = $this->Employees->create($data);
                    \Debug::log($employee, '$employee');
                }
                
                $count = count($rows);
                $importedCount = count($rows) - count($rowsNotInserted);
            }
            $this->template->load('employees/load.tpl',array(
                'imported'  =>  $imported,
                'total'     =>  $total,
            ));
        }
    }
}
