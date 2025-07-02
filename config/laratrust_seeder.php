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
        ],
        'administrador' => [
          'users' => 'b,r,e,a,d',
          'roles' => 'b,r,e,a,d',
          'permissions' => 'b,r',
          'departments' => 'b,r,e,a,d',
            'plants' => 'b,r,e,a,d',
            'sectors' => 'b,r,e,a,d',
        ],
        'supervisor' => [
            'users' => 'r',
            'roles' => 'r',
            'permissions' => 'r',
            'departments' => 'r',
            'plants' => 'r',
            'sectors' => 'r',
        ],
        'analista' => [
            'users' => 'r',
            'roles' => 'r',
            'permissions' => 'r',
            'departments' => 'r',
            'plants' => 'r',
            'sectors' => 'r',
        ],
    ],

     'permissions_map' => [
        'b' => 'browse',
        'r' => 'read',
        'e' => 'edit',
        'a' => 'add',
        'd' => 'delete',
    ],
];
