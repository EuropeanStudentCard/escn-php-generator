<?php
/**
 * This class is used to generate unique European Student Card Number (ESCN) for the "european student card"
 * <p>
 * This number is an UUID of 16 bytes (128 bits) and the generation algorithm is describe in RFC 4122 :
 * <p>
 * Octet 0-3: time_low The low field of the timestamp
 * <p>
 * Octet 4-5: time_mid The middle field of the timestamp
 * <p>
 * Octet 6-7: time_hi_and_version The high field of the timestamp multiplexed with the version number
 * <p>
 * Octet 8: clock_seq_hi_and_reserved The high field of the clock sequence multiplexed with the variant
 * <p>
 * Octet 9: clock_seq_low The low field of the clock sequence
 * <p>
 * Octet 10-15: node The spatially unique node identifier
 *
 */
class EuropeanStudentCard
{
        // Offset from 15 Oct 1582 to 1 Jan 1970
        const OFFSET_MILLIS = 12219292800000;
        
        private static $time = 0, $oldSysTime = 0;
        private static $clock, $hits = 0;
        
        /**
        * This method calculate an UUID from 2 parameters
        * @param prefix To distinguish servers of a same institution
        * @param pic Participant Identification Code
        * @return a unique ESCN
        */
        public static function getEscn($prefix, $pic) 
        {
                $node = self::getNode($prefix, $pic);
                
                if (--self::$hits > 0)
                        ++self::$time;
                else  
                {
                        $sysTime = round(microtime(true) * 1000);
                        
                        self::$hits = 10000;
                        
                        if ($sysTime <= self::$oldSysTime) 
                        {
                                if ($sysTime < self::$oldSysTime) // SYSTEM CLOCK WAS SET BACK
                                {
                                        self::$clock = (++$clock & 0x3fff) | 0x8000;
                                }
                                else // REQUESTING UUIDs TOO FAST FOR SYSTEM CLOCK
                                {
                                        usleep(1000);
                                        $sysTime = round(microtime(true) * 1000);
                                }
                        }
                        
                        self::$time = $sysTime * 10000 + self::OFFSET_MILLIS;
                        self::$oldSysTime = $sysTime;
                }
                
                $low = intval(self::$time);
                $mid = intval(self::$time >> 32) & 0xffff;
                
                // 12 bit hi, set high 4 bits to '0001' for RFC 4122 version 1
                $hi = (intval(self::$time >> 48) & 0x0fff) | 0x1000;
                
                return strtolower(sprintf("%08X-%04X-%04X-%04X-%s",
                        $low, $mid, $hi, self::$clock, $node
                ));
        }
        
        /**
        * This method calculate a node used for the ESCN creation and initiate the clock
        * node = Prefix + PIC
        *
        * @param intPrefix To distinguish servers of a same institution
        * @param pic Participant Identification Code
        * @return node
        * @throws "Invalid Prefix format"  if the prefix is malformed
        * @throws "Invalid PIC format"  if the PIC is malformed
        */
        private static function getNode($prefix, $pic) 
        {
                $prefix = str_pad($prefix, 3, '0', STR_PAD_LEFT);
                
                if (!preg_match("/[0-9]{3}/", $prefix))
                        throw new Exception('Invalid Prefix format!');
                
                if (!preg_match("/[0-9]{9}/", $pic))
                        throw new Exception('Invalid PIC format!');
                
                $concatId = $prefix . $pic;
                
                // 14 bit clock, set high 2 bits to '0001' for RFC 4122 variant 2
                self::$clock = intval((rand(0, 0x3fff)) | 0x8000);
                
                return $concatId;
        }
}
