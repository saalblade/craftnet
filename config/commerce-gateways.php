<?php

return [
   'stripe' => [
       'publishableKey' => getenv('STRIPE_PUBLIC_KEY'),
       'apiKey' => getenv('STRIPE_API_KEY'),
   ],
];
