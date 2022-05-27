<?php

namespace App\API\pln;

use Psr\Container\ContainerInterface;
use PDO;

class Plan {
    protected $container;
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $this->container->get('db');
    }
    
    public function addUsers($message_in) {
        
        $users = $message_in['params']['users'];
        $plan = $message_in['params']['plan'];
        $yearId = $this->container->get('session')->getSchoolYear()['id'];
        
        $this->db->beginTransaction();
        $sth = $this->db->prepare('INSERT INTO pln_plan_users(yearId, userId, dateFrom, dateTo, dayWeek, timeFrom, timeTo, activityType, activitySpecialist, activityComment)
VALUES(:yearId, :userId, STR_TO_DATE(:dateFrom, \'%Y-%m-%d\'), STR_TO_DATE(:dateTo, \'%Y-%m-%d\'), :dayWeek, :timeFrom, :timeTo, :activityType, :activitySpecialist, :activityComment)');
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        $sth->bindValue(':activityType', $plan['paramActivityType'], PDO::PARAM_INT);
        $sth->bindValue(':activitySpecialist', $plan['paramActivitySpecialist'], PDO::PARAM_INT);
        $sth->bindValue(':dateFrom', $plan['paramFrom'], PDO::PARAM_STR);
        $sth->bindValue(':dateTo', $plan['paramTo'], PDO::PARAM_STR);
        $sth->bindValue(':activityComment', $plan['paramActivityComment'], PDO::PARAM_STR);

        foreach($users AS $userId) {
            $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
            foreach($plan['time'] AS $weekDay => $item) {
                if($item['check']) {
                    switch ($weekDay) {
                        case 'mn':
                            $sth->bindValue(':dayWeek', 1, PDO::PARAM_INT);
                            break;
                        case 'tu':
                            $sth->bindValue(':dayWeek', 2, PDO::PARAM_INT);
                            break;
                        case 'we':
                            $sth->bindValue(':dayWeek', 3, PDO::PARAM_INT);
                            break;
                        case 'th':
                            $sth->bindValue(':dayWeek', 4, PDO::PARAM_INT);
                            break;
                        case 'fr':
                            $sth->bindValue(':dayWeek', 5, PDO::PARAM_INT);
                            break;
                        case 'sa':
                            $sth->bindValue(':dayWeek', 6, PDO::PARAM_INT);
                            break;
                        default:
                            $this->db->rollBack();
                            return [
                                'error' => [
                                    'code' => -32603,
                                    'message' => 'Internal error'
                                ]
                            ];
                    }
                    $sth->bindValue(':timeFrom', $item['paramFrom'], PDO::PARAM_STR);
                    $sth->bindValue(':timeTo', $item['paramTo'], PDO::PARAM_STR);
                    if(!$sth->execute()) {
                        $this->db->rollBack();
                        return [
                            'error' => [
                                'code' => -32603,
                                'message' => 'Internal error'
                            ]
                        ];
                    }
                }
            }
        }
        $this->db->commit();
        return [
            'result' => [
                'status' => 'ok'
            ]
        ];
    }
    
    public function addUser($message_in) {
        $userId = $message_in['params']['userId'];
        $dateFrom = $message_in['params']['dateFrom'];
        $timeFrom = $message_in['params']['timeFrom'];
        $dateTo = $message_in['params']['dateTo'];
        $timeTo = $message_in['params']['timeTo'];
        $weekDayNumber = $message_in['params']['weekDayNumber'];
        $activityType = $message_in['params']['activityType'];
        $activitySpecialist = $message_in['params']['activitySpecialist'];
        $activityComment = $message_in['params']['activityComment'];
        $yearId = $this->container->get('session')->getSchoolYear()['id'];
        
        $sth = $this->db->prepare('INSERT INTO pln_plan_users(yearId, userId, dateFrom, dateTo, dayWeek, timeFrom, timeTo, activityType, activitySpecialist, activityComment)
VALUES(:yearId, :userId, STR_TO_DATE(:dateFrom, \'%Y-%m-%d\'), STR_TO_DATE(:dateTo, \'%Y-%m-%d\'), :dayWeek, :timeFrom, :timeTo, :activityType, :activitySpecialist, :activityComment)');
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        $sth->bindValue(':activityType', $activityType, PDO::PARAM_INT);
        $sth->bindValue(':dayWeek', $weekDayNumber, PDO::PARAM_INT);
        $sth->bindValue(':activitySpecialist', $activitySpecialist, PDO::PARAM_INT);
        $sth->bindValue(':dateFrom', $dateFrom, PDO::PARAM_STR);
        $sth->bindValue(':dateTo', $dateTo, PDO::PARAM_STR);
        $sth->bindValue(':timeFrom', $timeFrom, PDO::PARAM_STR);
        $sth->bindValue(':timeTo', $timeTo, PDO::PARAM_STR);
        $sth->bindValue(':activityComment', $activityComment, PDO::PARAM_STR);
        
        if(!$sth->execute()) {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }
        return [
            'result' => [
                'status' => 'ok'
            ]
        ];
    }

    public function addClasses($message_in) {
        
        $classes = $message_in['params']['classes'];
        $plan = $message_in['params']['plan'];
        $yearId = $this->container->get('session')->getSchoolYear()['id'];
        
        $this->db->beginTransaction();
        $sth = $this->db->prepare('INSERT INTO pln_plan_classes(yearId, classId, dateFrom, dateTo, dayWeek, timeFrom, timeTo, activityType, activitySpecialist, activityComment)
VALUES(:yearId, :classId, STR_TO_DATE(:dateFrom, \'%Y-%m-%d\'), STR_TO_DATE(:dateTo, \'%Y-%m-%d\'), :dayWeek, :timeFrom, :timeTo, :activityType, :activitySpecialist, :activityComment)');
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        $sth->bindValue(':activityType', $plan['paramActivityType'], PDO::PARAM_INT);
        $sth->bindValue(':activitySpecialist', $plan['paramActivitySpecialist'], PDO::PARAM_INT);
        $sth->bindValue(':dateFrom', $plan['paramFrom'], PDO::PARAM_STR);
        $sth->bindValue(':dateTo', $plan['paramTo'], PDO::PARAM_STR);
        $sth->bindValue(':activityComment', $plan['paramActivityComment'], PDO::PARAM_STR);

        foreach($classes AS $classId) {
            $sth->bindValue(':classId', $classId, PDO::PARAM_INT);
            foreach($plan['time'] AS $weekDay => $item) {
                if($item['check']) {
                    switch ($weekDay) {
                        case 'mn':
                            $sth->bindValue(':dayWeek', 1, PDO::PARAM_INT);
                            break;
                        case 'tu':
                            $sth->bindValue(':dayWeek', 2, PDO::PARAM_INT);
                            break;
                        case 'we':
                            $sth->bindValue(':dayWeek', 3, PDO::PARAM_INT);
                            break;
                        case 'th':
                            $sth->bindValue(':dayWeek', 4, PDO::PARAM_INT);
                            break;
                        case 'fr':
                            $sth->bindValue(':dayWeek', 5, PDO::PARAM_INT);
                            break;
                        case 'sa':
                            $sth->bindValue(':dayWeek', 6, PDO::PARAM_INT);
                            break;
                        default:
                            $this->db->rollBack();
                            return [
                                'error' => [
                                    'code' => -32603,
                                    'message' => 'Internal error'
                                ]
                            ];
                    }
                    $sth->bindValue(':timeFrom', $item['paramFrom'], PDO::PARAM_STR);
                    $sth->bindValue(':timeTo', $item['paramTo'], PDO::PARAM_STR);
                    if(!$sth->execute()) {
                        $this->db->rollBack();
                        return [
                            'error' => [
                                'code' => -32603,
                                'message' => 'Internal error'
                            ]
                        ];
                    }
                }
            }
        }
        $this->db->commit();
        return [
            'result' => [
                'status' => 'ok'
            ]
        ];
     }
    
    private function getStartAndEndDate($week, $year) {
        $dto = new DateTime();
        $dto->setISODate($year, $week);
        $ret['week_start'] = $dto->format('Y-m-d');
        $dto->modify('+6 days');
        $ret['week_end'] = $dto->format('Y-m-d');
        return $ret;
    }
    
    private function getWeekDatesFromWeekNumber($number) {
        $schoolYearWeekBegin = $this->container->get('session')->getSchoolYear()['begindate'];
        $schoolYearWeekNumber = 1;
        while($schoolYearWeekBegin <= $this->container->get('session')->getSchoolYear()['enddate']) {
            $dayNumberOfWeek = $schoolYearWeekBegin->format('N');
            $schoolYearWeekEnd = clone $schoolYearWeekBegin;
            $schoolYearWeekEnd->modify('+' . (7 - $dayNumberOfWeek) . ' days');
            
            if($schoolYearWeekNumber == $number) {
                return [$schoolYearWeekBegin, $schoolYearWeekEnd];
            }
            
            $schoolYearWeekBegin->modify('+' . (8 - $dayNumberOfWeek) . ' days');
            $schoolYearWeekNumber += 1;
        }
        
        return null;
    }

    public function get($message_in) {
        $userId = (int)$message_in['params']['userId'];
        $weekNumber = (int)$message_in['params']['weekNumber'];
        $yearId = $this->container->get('session')->getSchoolYear()['id'];
        
        $weekDates = $this->getWeekDatesFromWeekNumber($weekNumber);
        
        if($weekDates == null) {
            return [
                'error' => [
                    'code' => -32602,
                    'message' => 'Неверный номер недели'
                ]
            ];
        }
        
        list($weekBeginDate, $weekEndDate) = $weekDates;

        $weekPlan = [
            1 => [],
            2 => [],
            3 => [],
            4 => [],
            5 => [],
            6 => []
        ];
        // Для ученика
        $sth = $this->db->prepare('SELECT id, dayWeek, activityType, activitySpecialist, timeFrom, timeTo, dateFrom, dateTo
FROM pln_plan_users WHERE yearId = :yearId AND userId = :userId'); // AND dateFrom <= :beginDate AND :endDate >= dateTo
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
//        $sth->bindValue(':beginDate', $weekBeginDate->format('Y-m-d'), PDO::PARAM_STR);
//        $sth->bindValue(':endDate', $weekEndDate->format('Y-m-d'), PDO::PARAM_STR);

        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $planBeginDate = new \DateTime($a_row['dateFrom'], new \DateTimeZone('GMT'));
                $planEndDate = new \DateTime($a_row['dateTo'], new \DateTimeZone('GMT'));
                list($planBegitHour, $planBeginMin) = explode(':', $a_row['timeFrom']);
                $planBeginDayPosition = (int)$planBegitHour * 60 + (int)$planBeginMin;
                list($planEndHour, $planEndMin) = explode(':', $a_row['timeTo']);
                $planEndDayPosition = (int)$planEndHour * 60 + (int)$planEndMin;
                $planLenTime = $planEndDayPosition - $planBeginDayPosition;
                
                $currDate = clone $weekBeginDate;
                $currDate->modify('+' . ($a_row['dayWeek'] - $currDate->format('N')) . ' days');
                if( ($planBeginDate <= $currDate) && ($planEndDate >= $currDate) && ($currDate <= $weekEndDate) ) { 
                    $weekPlan[$a_row['dayWeek']][] = [
                        'top' => $planBeginDayPosition,
                        'len' => $planLenTime,
                        'activityType' => (int)$a_row['activityType'],
                        'activitySpecialist' => (int)$a_row['activitySpecialist'],
                        'timeFrom' => $planBegitHour . ':' . $planBeginMin,
                        'timeTo' => $planEndHour . ':' . $planEndMin,
                        'id' => (int)$a_row['id'],
                        'planFor' => 'user'
                    ];
                }
            }
            $sth->closeCursor();
        }
        // Классы ученика
        $classes = [];
        $sth = $this->db->prepare('SELECT
classes.id AS clssId
FROM
users_classes
INNER JOIN classes ON classes.id = users_classes.classId
WHERE
classes.yearId = :yearId AND
users_classes.userId = :userId');
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
        $sth->bindValue(':userId', $userId, PDO::PARAM_INT);
        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $classes[] = $a_row['clssId'];
            }
            $sth->closeCursor();
        }
        // Для классов
        $sth = $this->db->prepare('SELECT id, classId, dayWeek, activityType, activitySpecialist, timeFrom, timeTo, dateFrom, dateTo
FROM pln_plan_classes WHERE yearId = :yearId AND classId IN (' . implode(', ', $classes) . ')'); // AND dateFrom <= :beginDate AND :endDate >= dateTo
        $sth->bindValue(':yearId', $yearId, PDO::PARAM_INT);
//        $sth->bindValue(':beginDate', $weekBeginDate->format('Y-m-d'), PDO::PARAM_STR);
//        $sth->bindValue(':endDate', $weekEndDate->format('Y-m-d'), PDO::PARAM_STR);

        if($sth->execute()) {
            while (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $planBeginDate = new \DateTime($a_row['dateFrom'], new \DateTimeZone('GMT'));
                $planEndDate = new \DateTime($a_row['dateTo'], new \DateTimeZone('GMT'));
                list($planBegitHour, $planBeginMin) = explode(':', $a_row['timeFrom']);
                $planBeginDayPosition = (int)$planBegitHour * 60 + (int)$planBeginMin;
                list($planEndHour, $planEndMin) = explode(':', $a_row['timeTo']);
                $planEndDayPosition = (int)$planEndHour * 60 + (int)$planEndMin;
                $planLenTime = $planEndDayPosition - $planBeginDayPosition;
                
                $currDate = clone $weekBeginDate;
                $currDate->modify('+' . ($a_row['dayWeek'] - $currDate->format('N')) . ' days');
                if( ($planBeginDate <= $currDate) && ($planEndDate >= $currDate) && ($currDate <= $weekEndDate) ) { 
                    $weekPlan[$a_row['dayWeek']][] = [
                        'top' => $planBeginDayPosition,
                        'len' => $planLenTime,
                        'activityType' => (int)$a_row['activityType'],
                        'activitySpecialist' => (int)$a_row['activitySpecialist'],
                        'timeFrom' => $planBegitHour . ':' . $planBeginMin,
                        'timeTo' => $planEndHour . ':' . $planEndMin,
                        'planFor' => 'class',
                        'id' => (int)$a_row['id'],
                        'classId' => (int)$a_row['classId'],
                    ];
                }
            }
            $sth->closeCursor();
        }
        
        // Сортировка от большей продолжительности к меньшей
        foreach ($weekPlan as &$value) {
            usort($value, function($a, $b) {
                if ($a['len'] == $b['len']) {
                    return 0;
                }
                return ($a['len'] < $b['len']) ? 1 : -1;
            });
        }
        
        return [
            'result' => [
                'status' => 'ok',
                'plan' => $weekPlan
            ]
        ];
    }
    
    public function getReport($message_in) {
        $planId = (int)$message_in['params']['planId'];
        $typePlan = $message_in['params']['planFor'];
        $report = [];
        switch ($typePlan) {
            case 'class':
                $sth = $this->db->prepare('SELECT yearId, dateFrom, dateTo, dayWeek, timeFrom, timeTo, activityType, activitySpecialist, activityComment FROM pln_plan_classes WHERE id = :id');
                break;
            case 'user':
                $sth = $this->db->prepare('SELECT yearId, dateFrom, dateTo, dayWeek, timeFrom, timeTo, activityType, activitySpecialist, userId, activityComment FROM pln_plan_users WHERE id = :id');
                break;
            default:
                return [
                    'error' => [
                        'code' => -32602,
                        'message' => 'Invalid params'
                ]
            ];
        }
        
        $sth->bindValue(':id', $planId, PDO::PARAM_INT);
        if($sth->execute()) {
            if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $report['dateFrom'] = (new \DateTime($a_row['dateFrom']))->format('d.m.Y');
                $report['dateTo'] = (new \DateTime($a_row['dateTo']))->format('d.m.Y');
                $report['timeFrom'] = $a_row['timeFrom'];
                $report['timeTo'] = $a_row['timeTo'];
                $report['activityType'] = (int)$a_row['activityType'];
                $report['activitySpecialist'] = (int)$a_row['activitySpecialist'];
                $report['yearId'] = (int)$a_row['yearId'];
                $report['activityComment'] = $a_row['activityComment'];
                if($typePlan == 'user') {
                    $report['userId'] = (int)$a_row['userId'];
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
        
        if(($typePlan == 'user') && ($report['activityType'] == 3) && ($report['activitySpecialist'] > 0)) {
            $sth = $this->db->prepare('SELECT
	users_base.surname, 
	users_base.firstname, 
	users_base.patronymic
FROM
	rbl_list
	INNER JOIN
	users_base
	ON 
            rbl_list.specialistUserId = users_base.id
WHERE
	rbl_list.yearId = :yearId AND
	rbl_list.specialistId = :specialistId AND
	rbl_list.userId = :userId ');
            $sth->bindValue(':yearId', $report['yearId'], PDO::PARAM_INT);
            $sth->bindValue(':specialistId', $report['activitySpecialist'], PDO::PARAM_INT);
            $sth->bindValue(':userId', $report['userId'], PDO::PARAM_INT);
            if($sth->execute()) {
                if (($a_row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                    $report['specialistFIO'] = $a_row['surname'] . ' ' . $a_row['firstname'] . ' ' . $a_row['patronymic'];
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
        }
        
        return [
            'result' => [
                'status' => 'ok',
                'report' => $report
            ]
        ];
    }
    
    public function delete($message_in) {
        $planId = (int)$message_in['params']['planId'];
        $typePlan = $message_in['params']['planFor'];
        
        switch ($typePlan) {
            case 'class':
                $sth = $this->db->prepare('DELETE FROM pln_plan_classes WHERE id = :id');
                break;
            case 'user':
                $sth = $this->db->prepare('DELETE FROM pln_plan_users WHERE id = :id');
                break;
            default:
                return [
                    'error' => [
                        'code' => -32602,
                        'message' => 'Invalid params'
                ]
            ];
        }
        
        $sth->bindValue(':id', $planId, PDO::PARAM_INT);
        
        if(!$sth->execute()) {
            return [
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error'
                ]
            ];
        }
        
        return [
            'result' => [
                'status' => 'ok'
            ]
        ];
    }
}
