<?php

return [
    'consumers' => [
        

             [
            'exchange' => 'planning_tickets',
            'queue' => 'planning_tickets.sync',
            'type' => 'direct',
            'routing_key' => 'planning_tickets.sync',
            'job' => \App\Jobs\SyncHelpdeskTicket::class,
        ],

        // Puedes agregar más consumidores aquí

    ],
];