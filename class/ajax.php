<?php
/**
 * A class that handles Ajax calls
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2017 Newcastle University
 *
 */
/**
 * Handles Ajax Calls.
 */
    class Ajax extends \Framework\Ajax
    {
        
/**
 * @var array Allowed operation codes. Values indicate : [needs login, Roles that user must have]
 */
        private static $ops = array(
            'addmod'        => array(TRUE, [['Site', 'Admin']]),
            'addchecker'    => array(TRUE, [['Site', 'Admin']]),
            'adduser'       => array(TRUE, [['Site', 'Admin']]),
            'checkpaper'    => array(TRUE, [['Site']])
        );
        
/**
 * Handle AJAX operations
 *
 * @param object	$context	The context object for the site
 *
 * @return void
 */
        public function handle($context)
        {
            
            $fdt = $context->formdata();
            $op = $fdt->post('op');
            # Handle my own stuff
            if (($name = $fdt->get('name', '')) !== '')
            { # Check to see if module name is already in use
                if (R::count('module', 'name=?', array($name)) > 0)
                {
                    return $context->web()->notfound(); // error if it exists....
                }
            }
            elseif (($lg = $fdt->get('login', '')) !== '')
            {   # Trying to register - handled by framework
                parent::handle($context);
            }
            elseif (($pid = $fdt->get('pid')) !== '')
            {
                # Admin is trying to assign checkers
                # Need to check that the user isn't assigning the module leader as checker - could use parsley?
                if (R::count('upload', 'id=?', array($pid)) > 0)
                {
                    return $context->web()->notfound(); // error if it exists....
                }
            }
            else
            {
                $op = $fdt->mustpost('op');
                if (isset(self::$ops[$op]))
                { # a valid operation
                    $curop = self::$ops[$op];
                    if ($curop[0])
                    { # this operation requires a logged in user
                        $context->mustbeuser();
                    }
                    $this->{$op}($context);
                }
                else
                {
                    # handle framework stuff
                    parent::handle($context);
                }
                
            }
            
        }
        
        /**
         * Adds a module to the site
         * 
         * @param object $context The context object for the site
         */
        private function addmod($context)
        {
            $fdt = $context->formdata();
            # Check fields to see if it is possible to add module with leader given
            if (($lg = $fdt->post('name')) !== '')
            { # Module name has been set
                $login = $fdt->mustpost('leader');
                if (($leader = R::findOne('user', 'login=?', array($login))) != NULL)
                {
                    $m = R::dispense('module');
                    $m->name = $fdt->mustpost('name');
                    $m->leader = $leader;
                    R::store($m);
                }   
            }
        }
        
        /**
         * Adds a checker to a module/paper
         * 
         * @param object $context The context object for the site
         */
        private function addchecker($context)
        {
            $fdt = $context->formdata();
            # Check fields to see if it is possible to add checker to module
            if (($pid = $fdt->post('pid')) !== '')
            { # Module name has been set
                $date = $fdt->mustpost('duedate');
                $login = $fdt->mustpost('clogin');
                if (($checker = R::findOne('user', 'login=?', array($login))) != NULL)
                { # Checker has been found in user table
                    if (($paper = R::findOne('upload', 'id=?', array($pid))) != NULL)
                    {
                        $c = R::dispense('checker');
                        $c->paper = $paper;
                        $c->checker = $checker;
                        $c->checked = 0;
                        #Format date into UTC
                        $c->due = $context->utcdate($date);
                        R::store($c);
                        
                        # Send an e-mail to the checker to inform them of this
                        $to = $c->email;
                        $msg = "You have been allocated " . $paper->filename . " to mark.";
                        $sender = $context->user()->email;
                        mail($to, 'You have been allocated a paper.', $msg, null, $sender);
                    }
                }
            }
        }
        
        /**
         * 'Overrides' add user in framework - I added more roles
         * 
         * @param object $context The context object for the site
         */
        private function adduser($context)
        {
            $now = $context->utcnow(); # make sure time is in UTC
            $fdt = $context->formdata();
            $u = R::dispense('user');
            $u->login = $fdt->mustpost('login');
            $u->email = $fdt->mustpost('email');
            $u->active = 1;
            $u->confirm = 1;
            $u->joined = $now;
            R::store($u);
            $u->setpw($fdt->mustpost('password'));
            
            // Add the relevant roles to the user
            if ($fdt->post('admin', 0) == 1)
            {
                $u->addrole('Site', 'Admin', '', $now);
            }
            if ($fdt->post('devel', 0) == 1)
            {
                $u->addrole('Site', 'Developer', '', $now);
            }            
            
            /* My own additional roles */
            if ($fdt->post('Teacher', 0) == 1)
            {
                $u->addrole('Site', 'Teacher', '', $now);
            }
            if ($fdt->post('Student', 0) == 1)
            {
                $u->addrole('Site', 'Student', '', $now);
            }
            if ($fdt->post('Administrator', 0) == 1)
            {
                $u->addrole('Site', 'Administrator', '', $now);
            }
            if ($fdt->post('External', 0) == 1)
            {
                $u->addrole('Site', 'External', '', $now);
            }
            
            echo $u->getID();
            
        }
        
        /**
         * Edits the checker table and marks a paper as checked
         * 
         * @param object $context The context object for this site
         */
        private function checkpaper($context)
        {
            $fdt = $context->formdata();
            
            $bn = $context->load('checker', $fdt->mustpost('id'), Context::R400);
            $bn->checked = 1;
            R::store($bn);
        }
        
    }
?>