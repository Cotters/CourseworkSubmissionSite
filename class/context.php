<?php
/**
 * A wrapper so that users dont need to edit the FWContext class in order to add features.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2016 Newcastle University
 *
 */
/**
 * A wrapper for the real Context class that allows people to extend it's functionality
 * in ways that are apporpriate for their particular website.
 */
    class Context extends \Framework\Context
    {
        
/**
 * Do we have a logged in external user?
 *
 * @return boolean
 */
        public function hasexternal()
        {
            return $this->hasuser() && $this->user()->ismoderator();
        }
        
/**
 * Do we have a logged in teacher (module leader) user?
 *
 * @return boolean
 */
        public function hasteacher()
        {
            return $this->hasuser() && $this->user()->isteacher();
        }
        
/**
 * Do we have a logged in student user?
 *
 * @return boolean
 */
        public function hasstudent()
        {
            return $this->hasuser() && $this->user()->isstudent();
        }
        
        
    }
?>
