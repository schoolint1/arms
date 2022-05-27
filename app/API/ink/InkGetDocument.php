<?php

namespace App\API;

use Psr\Container\ContainerInterface;
use PhpOffice\PhpWord\Element\Field;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PDO;

class InkGetDocument
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    use InkHelpers;

    public function getuserdocumect($message_in) {
        $user = $this->container->get('session')->getUser();
        $userId = (int)$message_in['params']['userId'];
        $yearId = $this->container->get('session')->getSchoolYear()['id'];
        $commissionNumber = (int)$message_in['params']['commissionNum'];
        $db = $this->container->get('db');

        // Информация о комиссии
        $sth = $db->prepare('SELECT firstDate, secondDate, thirdDate FROM ink_commissions WHERE id = :id');
        $sth->bindValue(':id', $yearId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                switch ($commissionNumber) {
                    case 1:
                        $commission_date = new \DateTime($a_row['firstDate']);
                        break;
                    case 2:
                        $commission_date = new \DateTime($a_row['secondDate']);
                        break;
                    case 3:
                        $commission_date = new \DateTime($a_row['thirdDate']);
                        break;
                }
            } else {
                return [
                    'error' => [
                        'code' => -32602,
                        'message' => 'Invalid params'
                    ]
                ];
            }
            $sth->closeCursor();
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }
        // Информация о ребёнке
        $sth = $db->prepare('SELECT users_base.surname, users_base.firstname, users_base.patronymic, users_base.gender, users_base.birthday,
users_extend.addrCityName, users_extend.addrCityType, users_extend.addrStreetName, users_extend.addrStreetType, users_extend.addrHouse, users_extend.addrFlat
FROM users_base 
LEFT JOIN users_extend ON users_extend.userBaseId = users_base.id
WHERE users_base.id = :id');
        $sth->bindValue(':id', $userId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $fio = $a_row['surname'] . ' ' . $a_row['firstname'] . ' ' . $a_row['patronymic'];
                $birthday = new \DateTime($a_row['birthday']);
                $age = $birthday->diff(new \DateTime);
                $addr = ((strlen($a_row['addrCityType']) > 0) ? $a_row['addrCityType'] .'. ' : '') .
                    $a_row['addrCityName'] . ', ' .
                    ((strlen($a_row['addrStreetType']) > 0) ? $a_row['addrStreetType'] . '. ' : '').
                    $a_row['addrStreetName'] . ', ' . $a_row['addrHouse'] .
                    ((strlen($a_row['addrFlat']) > 0) ? '-' . $a_row['addrFlat']: '');
            } else {
                return [
                    'error' => [
                        'code' => -32602,
                        'message' => 'Invalid params'
                    ]
                ];
            }
            $sth->closeCursor();
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }

        // Класс ребёнка
        $sth = $db->prepare('SELECT classes.`name`, users_base.surname, users_base.firstname, users_base.patronymic
FROM classes
INNER JOIN users_classes ON users_classes.classId = classes.id
LEFT JOIN users_base ON users_base.id = classes.teacherId
WHERE classes.yearId = :yearId AND users_classes.userId = :userId');
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $class = $a_row['name'];
                $classmaster_name = $a_row['surname'] . '  ' . $a_row['firstname'] . ' ' . $a_row['patronymic'];
            } else {
                return [
                    'error' => [
                        'code' => -32602,
                        'message' => 'Invalid params'
                    ]
                ];
            }
            $sth->closeCursor();
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }
        // Данные комиссии
        $commission_data = [];
        $sth = $db->prepare('SELECT id, `name` FROM ink_commission_groups ORDER BY orderNum');
        if($sth->execute()) {
            while ($a_row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $commission_data[$a_row['id']] = [
                    'id' => $a_row['id'],
                    'name' => $this->str_helper([$classmaster_name, ''], $a_row['name']),
                    'short name' => trim($this->str_helper(['', ''], $a_row['name'])),
                    'count' => 0,
                    'parameters' => [],
                ];
            }
            $sth->closeCursor();
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }
        $sth = $db->prepare('SELECT id, groupId, `name`, isFirstCommissionAccess, isSecondCommissionAccess, isThirdCommissionAccess FROM ink_commission_parameters ORDER BY orderNum');
        if($sth->execute()) {
            while ($a_row = $sth->fetch(PDO::FETCH_ASSOC)) {
                if( ((($a_row['isFirstCommissionAccess'] > 0) && ($commissionNumber == 1)) ||
                    (($a_row['isSecondCommissionAccess'] > 0) && ($commissionNumber == 2)) ||
                    (($a_row['isThirdCommissionAccess'] > 0) && ($commissionNumber == 3))) &&
                    array_key_exists($a_row['groupId'], $commission_data) ) {
                        $commission_data[$a_row['groupId']]['parameters'][$a_row['id']] = [
                            'id' => $a_row['id'],
                            'name' => $this->str_helper([$classmaster_name, ''], $a_row['name']),
                            'count' => 0,
                            'data' => '',
                        ];
                }
            }
            $sth->closeCursor();
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }
        $commission_users = [];
        $sth = $db->prepare('SELECT val, parameterId, specialistId FROM ink_commission_data WHERE commissionNum = :commissionNum AND commissionId = :commissionId AND userId = :userId');
        $sth->bindValue(':commissionNum', $commissionNumber, PDO::PARAM_INT);
        $sth->bindValue(':commissionId', $yearId, PDO::PARAM_INT);
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        if($sth->execute()) {
            while ($a_row = $sth->fetch(PDO::FETCH_ASSOC)) {
                foreach ($commission_data AS $commission_data_key => $commission_data_val) {
                    foreach ($commission_data_val['parameters'] AS $commission_data_parameter_key => $commission_data_parameter_val) {
                        if($commission_data_parameter_val['id'] == $a_row['parameterId']) {
                            $commission_data[$commission_data_key]['parameters'][$commission_data_parameter_key]['data'] = $a_row['val'];
                            $commission_data[$commission_data_key]['parameters'][$commission_data_parameter_key]['specialist'] = $a_row['specialistId'];
                            $commission_users[$a_row['specialistId']] = '';
                            $commission_data[$commission_data_key]['parameters'][$commission_data_parameter_key]['count'] += 1;
                            $commission_data[$commission_data_key]['count'] += 1;
                        }
                    }
                }
            }
            $sth->closeCursor();
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }
        // Специалисты в комиссии
        $sth = $db->prepare('SELECT id, surname, firstname, patronymic FROM users_base WHERE id IN(' . implode(', ', array_keys($commission_users)) . ')');
        if($sth->execute()) {
            while ($a_row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $commission_users[$a_row['id']] = mb_substr($a_row['firstname'], 0, 1) . '. ' . mb_substr($a_row['patronymic'], 0, 1) . '. ' . $a_row['surname'];
            }
            $sth->closeCursor();
        } else {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(__DIR__ . '/../../resources/ink/user.docx');
        $templateProcessor->setValue('commission_date', $commission_date->format('d.m.Y'));
        $templateProcessor->setValue('fio', $fio);
        $templateProcessor->setValue('birthday', $birthday->format('d.m.Y'));
        $templateProcessor->setValue('age',  $age->y);
        $templateProcessor->setValue('class',  $class);
        $templateProcessor->setValue('addr',  $addr);
        // Create a new table style
        $table_style = new \PhpOffice\PhpWord\Style\Table;
        $table_style->setUnit(\PhpOffice\PhpWord\Style\Table::WIDTH_PERCENT);
        $table_style->setWidth(100 * 50);

        $table_data = new Table($table_style);
        $table_specialists = new Table($table_style);
        $commission_list = [];
        // Данные комиссии
        foreach ($commission_data AS $commission_data_val) {
            if($commission_data_val['count'] > 0) {
                $table_data->addRow();
                $table_data->addCell()->addText($commission_data_val['name'], ['bold' => true]);
                foreach ($commission_data_val['parameters'] AS $commission_data_parameter_val) {
                    if($commission_data_parameter_val['count'] > 0) {
                        if($commission_data_val['name'] != $commission_data_parameter_val['name']) {
                            $table_data->addRow();
                            $table_data->addCell()->addText($commission_data_parameter_val['name'], ['bold' => true]);
                        }
                        $table_data->addRow();
                        $table_data->addCell()->addText($commission_data_parameter_val['data']);
                        
                        if($commission_data_parameter_val['specialist'] > 0) {
                            if(array_key_exists($commission_data_val['id'], $commission_list)) {
                                if(!array_key_exists($commission_data_parameter_val['specialist'], $commission_list[$commission_data_val['id']])) {
                                    $commission_list[$commission_data_val['id']][] = $commission_data_parameter_val['specialist'];
                                }
                            } else {
                                $commission_list[$commission_data_val['id']] = [];
                                $commission_list[$commission_data_val['id']][] = $commission_data_parameter_val['specialist'];
                            }
                        }
                    }
                }
            }
        }
        
        // Список членов комиссии
        foreach ($commission_list as $commission_list_id => $commission_list_val) {
            foreach($commission_list_val as $commission_list_specialist) {
                $table_specialists->addRow();
                $table_specialists->addCell()->addText($commission_data[$commission_list_id]['short name']);
                $table_specialists->addCell()->addText($commission_users[$commission_list_specialist]);
                $table_specialists->addCell()->addText('___________');
            }
        }
        $table_specialists->addRow();
        $table_specialists->addCell()->addText('');
        $table_specialists->addCell()->addText('');
        $table_specialists->addCell()->addText('');
        $table_specialists->addRow();
        $table_specialists->addCell()->addText('Председатель психолого-медико-педагогической консилиума');
        $table_specialists->addCell()->addText('Р.В.Кузьмин');
        $table_specialists->addCell()->addText('___________');
        
        $table_specialists->addRow();
        $table_specialists->addCell()->addText('');
        $table_specialists->addCell()->addText('');
        $table_specialists->addCell()->addText('');
        $table_specialists->addRow();
        $table_specialists->addCell()->addText('Заместитель председателя психолого-медико-педагогической консилиума');
        $table_specialists->addCell()->addText('З.Г. Маматова');
        $table_specialists->addCell()->addText('___________');
        
        $table_specialists->addRow();
        $table_specialists->addCell()->addText('');
        $table_specialists->addCell()->addText('');
        $table_specialists->addCell()->addText('');
        $table_specialists->addRow();
        $table_specialists->addCell()->addText('Секретарь психолого-медико-педагогической консилиума');
        $table_specialists->addCell()->addText('А.Е. Бабий');
        $table_specialists->addCell()->addText('___________');

        $templateProcessor->setComplexBlock('data', $table_data);
        $templateProcessor->setComplexBlock('specialists', $table_specialists);
        
        
        $filename = uniqid();
        $templateProcessor->saveAs(__DIR__ . '/../../public/download/' . $filename . '.docx');

        return [
            'result' => [
                'status' => 'ok',
                'uri' => 'download/' . $filename . '.docx'
            ]
        ];
    }
}