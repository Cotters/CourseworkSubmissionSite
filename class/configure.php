<?php
    use \Framework\Local as Local;
    use \R as R;
    
/**
 * Defintion of Configure class
 * 
 * Handles /configure action - allowing the administrator to configure the system
**/

    class Configure extends \Framework\SiteAction
    {

        /**
         * Gets all users that are also teachers (module leaders)
         * 
         * @return array Array of user beans
         */
        private function getleaders()
        {
            $query = R::getAll("SELECT U.* FROM user U, role R, rolename RN WHERE RN.name = 'Teacher' AND RN.id = R.rolename_id AND U.id = R.user_id");
            return $leaders = R::convertToBeans('leaders', $query);
        }
        
        /**
         * Gets a list of all users that can be allocated a paper
         * 
         * @return object - A bean object containing a list of users
         */
        private function getposscheckers()
        {
            $query = R::getAll("SELECT U.* FROM user U WHERE u.id in (SELECT R.user_id FROM role R WHERE R.rolename_id in (SELECT RN.id FROM rolename RN WHERE RN.name = 'Teacher' OR RN.name = 'External'))");
            return R::convertToBeans('users', $query);
        }
        
        
        /**
         * Add a module
         * 
         * @param object $context The context object for this site
         */
        private function modules($context)
        {
            # Fetch all modules to show on the modules page
            $modules = R::findAll('module');
            
            # Select all module names and their leaders' login
            $query = R::getAll("SELECT M.id, M.name, U.login FROM module M INNER JOIN user U WHERE m.leader_id = u.id");
            $modmap = R::convertToBeans('modmap', $query);
            
            # Fetch all users who can be assigned to the module
            $leaders = $this->getleaders();
            $context->local()->addval('leaders', $leaders);
            $context->local()->addval('modmap', $modmap);
            
            return 'modules.twig';
        }
        
        private function checkers($context)
        {
            
            # Get checker login and filenames and store in one array/map
            $query = R::getAll("SELECT C.id, U.login, P.filename FROM user U INNER JOIN upload P INNER JOIN checker C WHERE C.checker_id = U.id AND C.paper_id = P.id");
            $checkmap = R::convertToBeans('checkmap', $query);
            
            # Assign checkers to any paper ? or just exam papers
            $papers = R::findAll('upload');
            
            # Gets all users that can be allocated a paper
            $users = $this->getposscheckers();
            
            $context->local()->addval('users', $users);
            $context->local()->addval('papers', $papers);
            $context->local()->addval('checkmap', $checkmap);
            
            return 'checkers.twig';
        }
        
        
        /**
         * Allow the admin to configure the system
         * 
         * @param object $context The context object for the site.
         */
        public function handle($context)
        {
            
            if (!$context->hasadmin())
            { # Admin is the only one who can configure the system
                return 'error/403.twig';
            }
            $rest = $context->rest();
            switch ($rest[0])
            {
                case 'modules':
                    return $this->modules($context);
                case 'checkers':
                    return $this->checkers($context);
                case 'edit':
                    // Editing something - module
                    if (count($rest) < 3)
                    {
                        return 'error/404.twig';
                        /* NOT REACHED */
                    }
                    $kind = $rest[1];
                    $obj = $context->load($kind, $rest[2]);
                    if (!is_object($obj))
                    {
                        return 'error/404.twig';
                        /* NOT REACHED */
                    }
                    if (($bid = $context->formdata()->post('bean', '')) !== '')
                    { # this is a post
                        if ($bid != $obj->getID())
                        { # something odd...
                            return 'error/500.twig';
                            /* NOT REACHED */
                        }
                        $obj->edit($context);
                        // The edit call might divert to somewhere else so sometimes we may not get here.
                    }
                    // Get the leader of this module
                    $leader = $context->load('user', $obj->leader_id);
                    $leaders = $this->getleaders();
                    
                    $context->local()->addval('bean', $obj);
                    $context->local()->addval('leader', $leader);
                    $context->local()->addval('leaders', $leaders);
                    
                    return 'edit'.$kind.'.twig';
                default:
                    return 'configure.twig';
            }            
        }
    
    }

?>
