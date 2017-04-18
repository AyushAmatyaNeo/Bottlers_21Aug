<?php

/**
 * Created by PhpStorm.
 * User: punam
 * Date: 10/4/16
 * Time: 4:59 PM
 */

namespace ManagerService;

use Zend\Router\Http\Segment;
use Application\Controller\ControllerFactory;

return [
    'router' => [
        'routes' => [
            'leaveapprove' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/managerservice/leaveapprove[/:action[/:id][/:role]]',
                    'defaults' => [
                        'controller' => Controller\LeaveApproveController::class,
                        'action' => 'index'
                    ]
                ]
            ],
            'attedanceapprove' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/managerservice/attendanceapprove[/:action[/:id][/:role]]',
                    'defaults' => [
                        'controller' => Controller\AttendanceApproveController::class,
                        'action' => 'index'
                    ]
                ]
            ],
            'loanApprove' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/managerservice/loanApprove[/:action[/:id][/:role]]',
                    'defaults' => [
                        'controller' => Controller\LoanApproveController::class,
                        'action' => 'index'
                    ]
                ]
            ],
            'advanceApprove' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/managerservice/advanceApprove[/:action[/:id][/:role]]',
                    'defaults' => [
                        'controller' => Controller\AdvanceApproveController::class,
                        'action' => 'index'
                    ]
                ]
            ],
            'salaryReview' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/managerservice/salaryreview[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\SalaryReviewController::class,
                        'action' => 'index'
                    ]
                ]
            ],
            'travelApprove' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/managerservice/travelApprove[/:action[/:id][/:role]]',
                    'defaults' => [
                        'controller' => Controller\TravelApproveController::class,
                        'action' => 'index'
                    ]
                ]
            ],
            'dayoffWorkApprove' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/managerservice/dayoffWorkApprove[/:action[/:id][/:role]]',
                    'defaults' => [
                        'controller' => Controller\DayoffWorkApproveController::class,
                        'action' => 'index'
                    ]
                ]
            ],
            'holidayWorkApprove' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/managerservice/holidayWorkApprove[/:action[/:id][/:role]]',
                    'defaults' => [
                        'controller' => Controller\HolidayWorkApproveController::class,
                        'action' => 'index'
                    ]
                ]
            ],
            'trainingApprove' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/managerservice/trainingApprove[/:action[/:id][/:role]]',
                    'defaults' => [
                        'controller' => Controller\TrainingApproveController::class,
                        'action' => 'index'
                    ]
                ]
            ],
        ]
    ],
    'navigation' => [
        'leaveapprove' => [
                [
                'label' => 'Leave Request',
                'route' => 'leaveapprove',
            ],
                [
                'label' => 'Leave Request',
                'route' => 'leaveapprove',
                'pages' => [
                        [
                        'label' => 'List',
                        'route' => 'leaveapprove',
                        'action' => 'index',
                    ],
                        [
                        'label' => 'List',
                        'route' => 'leaveapprove',
                        'action' => 'status',
                    ],
                        [
                        'label' => 'Edit',
                        'route' => 'leaveapprove',
                        'action' => 'edit',
                    ],
                        [
                        'label' => 'View',
                        'route' => 'leaveapprove',
                        'action' => 'view',
                    ],
                ]
            ]
        ],
        'attedanceapprove' => [
                [
                'label' => 'Attendance Request',
                'route' => 'attedanceapprove',
            ],
                [
                'label' => 'Attendance Request',
                'route' => 'attedanceapprove',
                'pages' => [
                        [
                        'label' => 'List',
                        'route' => 'attedanceapprove',
                        'action' => 'index',
                    ],
                        [
                        'label' => 'List',
                        'route' => 'attedanceapprove',
                        'action' => 'status',
                    ],
                        [
                        'label' => 'View',
                        'route' => 'attedanceapprove',
                        'action' => 'view',
                    ],
                ]
            ]
        ],
        'loanApprove' => [
                [
                'label' => 'Loan Request',
                'route' => 'loanApprove',
            ],
                [
                'label' => 'Loan Request',
                'route' => 'loanApprove',
                'pages' => [
                        [
                        'label' => 'List',
                        'route' => 'loanApprove',
                        'action' => 'index',
                    ],
                        [
                        'label' => 'List',
                        'route' => 'loanApprove',
                        'action' => 'status',
                    ],
                        [
                        'label' => 'View',
                        'route' => 'loanApprove',
                        'action' => 'view',
                    ],
                ]
            ]
        ],
        'advanceApprove' => [
                [
                'label' => 'Advance Request',
                'route' => 'advanceApprove',
            ],
                [
                'label' => 'Advance Request',
                'route' => 'advanceApprove',
                'pages' => [
                        [
                        'label' => 'List',
                        'route' => 'advanceApprove',
                        'action' => 'index',
                    ],
                        [
                        'label' => 'List',
                        'route' => 'advanceApprove',
                        'action' => 'status',
                    ],
                        [
                        'label' => 'View',
                        'route' => 'advanceApprove',
                        'action' => 'view',
                    ],
                ]
            ]
        ],
        'salaryReview' => [
                [
                'label' => 'SalaryReview',
                'route' => 'salaryReview',
            ],
                [
                'label' => 'SalaryReview',
                'route' => 'salaryReview',
                'pages' => [
                        [
                        'label' => 'List',
                        'route' => 'salaryReview',
                        'action' => 'index',
                    ], [
                        'label' => 'Add',
                        'route' => 'salaryReview',
                        'action' => 'add',
                    ],
                        [
                        'label' => 'Edit',
                        'route' => 'loanApprove',
                        'action' => 'edit',
                    ],
                ]
            ]
        ],
        'travelApprove' => [
                [
                'label' => 'Travel Request',
                'route' => 'travelApprove',
            ],
                [
                'label' => 'Travel Request',
                'route' => 'travelApprove',
                'pages' => [
                        [
                        'label' => 'List',
                        'route' => 'travelApprove',
                        'action' => 'index',
                    ],
                        [
                        'label' => 'List',
                        'route' => 'travelApprove',
                        'action' => 'status',
                    ],
                        [
                        'label' => 'View',
                        'route' => 'travelApprove',
                        'action' => 'view',
                    ],
                    [
                        'label' => 'View',
                        'route' => 'travelApprove',
                        'action' => 'expenseDetail',
                    ],
                ]
            ]
        ],
        'dayoffWorkApprove' => [
                [
                'label' => 'Work on Day-off Request',
                'route' => 'dayoffWorkApprove',
            ],
                [
                'label' => 'Work on Day-off Request',
                'route' => 'dayoffWorkApprove',
                'pages' => [
                        [
                        'label' => 'List',
                        'route' => 'dayoffWorkApprove',
                        'action' => 'index',
                    ],
                        [
                        'label' => 'List',
                        'route' => 'dayoffWorkApprove',
                        'action' => 'status',
                    ],
                        [
                        'label' => 'View',
                        'route' => 'dayoffWorkApprove',
                        'action' => 'view',
                    ],
                ]
            ]
        ],
        'holidayWorkApprove' => [
                [
                'label' => 'Work on Holiday Request',
                'route' => 'holidayWorkApprove',
            ],
                [
                'label' => 'Work on Holiday Request',
                'route' => 'holidayWorkApprove',
                'pages' => [
                        [
                        'label' => 'List',
                        'route' => 'holidayWorkApprove',
                        'action' => 'index',
                    ],
                        [
                        'label' => 'List',
                        'route' => 'holidayWorkApprove',
                        'action' => 'status',
                    ],
                        [
                        'label' => 'View',
                        'route' => 'holidayWorkApprove',
                        'action' => 'view',
                    ],
                ]
            ]
        ],
        'trainingApprove' => [
                [
                'label' => 'Training Request',
                'route' => 'trainingApprove',
            ],
                [
                'label' => 'Training Request',
                'route' => 'trainingApprove',
                'pages' => [
                        [
                        'label' => 'List',
                        'route' => 'trainingApprove',
                        'action' => 'index',
                    ],
                        [
                        'label' => 'List',
                        'route' => 'trainingApprove',
                        'action' => 'status',
                    ],
                        [
                        'label' => 'View',
                        'route' => 'trainingApprove',
                        'action' => 'view',
                    ],
                ]
            ]
        ],
        'salaryReview' => [
                [
                'label' => 'Salary Review',
                'route' => 'salaryReview',
            ],
                [
                'label' => 'Salary Review',
                'route' => 'salaryReview',
                'pages' => [
                        [
                        'label' => 'List',
                        'route' => 'salaryReview',
                        'action' => 'index',
                    ],
                        [
                        'label' => 'Add',
                        'route' => 'salaryReview',
                        'action' => 'add',
                    ],
                        [
                        'label' => 'Edit',
                        'route' => 'salaryReview',
                        'action' => 'edit',
                    ],
                ]
            ]
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\LeaveApproveController::class => ControllerFactory::class,
            Controller\AttendanceApproveController::class => ControllerFactory::class,
            Controller\LoanApproveController::class => ControllerFactory::class,
            Controller\SalaryReviewController::class => ControllerFactory::class,
            Controller\AdvanceApproveController::class => ControllerFactory::class,
            Controller\TravelApproveController::class => ControllerFactory::class,
            Controller\DayoffWorkApproveController::class => ControllerFactory::class,
            Controller\HolidayWorkApproveController::class => ControllerFactory::class,
            Controller\TrainingApproveController::class => ControllerFactory::class
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];


