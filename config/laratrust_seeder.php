<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        'superadministrador' => [
              'users' => 'b,r,e,a,d',
              'roles' => 'b,r,e,a,d',
              'permissions' => 'b,r,e,a,d',
              'departments' => 'b,r,e,a,d',
              'plants' => 'b,r,e,a,d',
              'sectors' => 'b,r,e,a,d',
              'article_types' => 'b,r,e,a,d',
              'articles' => 'b,r,e,a,d',
              'files' => 'b,r,e,a,d',
              'iers' => 'b,r,e,a,d',
              'areas' => 'b,r,e,a,d',
              'tasks' => 'b,r,e,a,d,s',
              'task_plans' => 'b,r,e,a,d',

        ],
        'administrador' => [
          'users' => 'b,r,e,a,d',
          'roles' => 'b,r,e,a,d',
          'permissions' => 'b,r',
          'departments' => 'b,r,e,a,d',
            'plants' => 'b,r,e,a,d',
            'sectors' => 'b,r,e,a,d',
            'article_types' => 'b,r,e,a,d',
            'articles' => 'b,r,e,a,d',
            'files' => 'b,r,e,a,d',
            'iers' => 'b,r,e,a,d',
            'areas' => 'b,r,e,a,d',
             'task_plans' => 'b,r,e,a,d',
            'tasks' => 'b,r,e,a,d,s',



        ],
        'supervisor' => [
            'users' => 'b,r',
            'roles' => 'b,r',
            'permissions' => 'b,r',
            'departments' => 'b,r',
            'plants' => 'b,r',
            'sectors' => 'b,r',
            'article_types' => 'b,r',
            'articles' => 'b,r',
            'files' => 'b,r',
            'iers' => 'b,r',
            'areas' => 'b,r',
            'tasks' => 'b,r,e,a,d,s',
            'task_plans' => 'b,r,e,a,d',



        ],
        'analista' => [
            'users' => 'b,r',
            'roles' => 'b,r',
            'permissions' => 'b,r',
            'departments' => 'b,r',
            'plants' => 'b,r',
            'sectors' => 'b,r',
            'article_types' => 'b,r',
            'articles' => 'b,r',
            'files' => 'b,r',
            'areas' => 'b,r',


        ],
    ],

     'permissions_map' => [
        'b' => 'browse',
        'r' => 'read',
        'e' => 'edit',
        'a' => 'add',
        'd' => 'delete',
        's' => 'supervise',
    ],
];
