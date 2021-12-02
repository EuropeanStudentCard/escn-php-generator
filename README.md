# ESCN Generator PHP Library

## Goal
This library can be used to generate unique European Student Card Number (ESCN) for the "european student card" partners.

## Algorithm

The used algorithm to generate the ESCN is the RFC-4122 version 1. However, as allowed by the RFC, the number the physical address (MAC) used to ensure the uniqueness of the ESCN is replaced by another number of 48 bits :

- A ```prefix``` (3 digits positive integer) to distinguish servers of a same institution
- The ```Participant Identification Code (PIC)``` of the institution.  

## ESCN Structure
The ESCN is a UUID of 16 bytes
* Octet 0-3: time_low The low field of the timestamp
* Octet 4-5: time_mid The middle field of the timestamp
* Octet 6-7: time_hi_and_version The high field of the timestamp multiplexed with the version number
* Octet 8: clock_seq_hi_and_reserved The high field of the clock sequence multiplexed with the variant
* Octet 9: clock_seq_low The low field of the clock sequence
* Octet 10-15: node The spatially unique node identifier ** Prefix + PIC ** 
