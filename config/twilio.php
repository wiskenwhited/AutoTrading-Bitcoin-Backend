<?php

return [

    'twilio' => [
        'sid' => getenv('TWILIO_SID') ?: 'AC2667ab332abf5772a6fc52266ccd2d72',
        'token' => getenv('TWILIO_TOKEN') ?: '671a7b5ae0e3dba4449c948c047a60cd',
        'from' => getenv('TWILIO_FROM') ?: '+18582957384',
    ],
];
