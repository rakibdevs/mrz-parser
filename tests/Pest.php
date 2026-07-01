<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Sample MRZ strings (ICAO 9303 fictional "Utopia" documents)
|--------------------------------------------------------------------------
*/

const MRZ_PASSPORT = "P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\n"
    . 'L898902C36UTO7408122F1204159ZE184226B<<<<<10';

const MRZ_VISA_44 = "V<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<\n"
    . 'L8988901C4XXX7408122F96121096ZE184226B<<<<<<';

const MRZ_VISA_36 = "V<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\n"
    . 'L8988901C4XXX7408122F9612109<<<<<<<<';

const MRZ_TD1 = "I<UTOD231458907<<<<<<<<<<<<<<<\n"
    . "7408122F1204159UTO<<<<<<<<<<<6\n"
    . 'ERIKSSON<<ANNA<MARIA<<<<<<<<<<';

const MRZ_TD2 = "I<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<\n"
    . 'D231458907UTO7408122F1204159<<<<<<<6';
