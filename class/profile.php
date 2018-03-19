<?php
    
    
    class Profile extends \Framework\SiteAction
    {
        /**
         * List past papers on users profile
         * @param object $context The context object for the site
         *                        
         * @return string Template name
         */
        public function handle($context)
        {
            
            $rest = $context->rest()[0];
            if ($rest == 'papers')
            {
                return $this->papers($context);
            }
            return 'profile.twig';
        }
        
        /**
         * Returns a template with a list of relevant papers
         * 
         * @param  object $context The context object for this site
         * @return string Twig template
         */
        private function papers($context)
        {
            $fdt = $context->formdata();
            if ($fname = $fdt->get('search') !== '')
            {
                # User is trying to download a file
                $file = R::findOne('upload', 'filename=?', $fname);
                $context->local()->addval('download', $fname);
            }
            
            $userps = $this->getuserpapers($context->user());
            $alps = $this->getallocpapers($context->user());
            $pastps = $this->getpastpapers();
            $admps = $this->getadminpapers($context->user());

            $context->local()->addval('userpapers', $userps);
            $context->local()->addval('allocpapers', $alps);
            $context->local()->addval('pastpapers', $pastps);
            $context->local()->addval('adminpapers', $admps);
            
            return 'papers.twig';
        }
        
        /**
         * Gets an array of papers that correspond to the users role
         * 
         * @param  object $user The currently signed in user
         *                                                    
         * @return object  Bean object of all papers in upload that belong to the user
         */
        private function getuserpapers($user)
        {            
            # Teacher is the only user that can upload papers (module leader)
            if ($user->isteacher())
            { # Shows all the papers the teacher has uploaded
                $papers = R::find('upload', 'user_id=?', array($user->getID()));
            }
            else
            {
                # Students and externals, for example, wont have any uploaded papers
                $papers = [];
            }
            
            return $papers;
        }
        
        
        /**
         * Get the array of papers that have been allocated to the user and are still to be checked
         * 
         * @param object $user The currently signed in user
         *                     
         * @return array  Array of upload rows plus the checker
         */
        private function getallocpapers($user)
        {             
            # Get checker id and allocated papers and store in one array/map
            $query = R::getAll("SELECT P.*, C.* FROM upload P INNER JOIN checker C WHERE C.checked = 0 AND C.checker_id =? AND C.paper_id = P.id", array($user->getID()));
            $checkmap = R::convertToBeans('checkmap', $query);
            
            return $checkmap;
        }
        
        /**
         * Gets all the papers before the start of the academic year. (Classified as past paper.)
         * 
         * @return object Contains papers from the upload table
         */
        private function getpastpapers()
        {
            $ayear = $this->getacadyear();
            
            $papers = R::find('upload', 'added<?', array($ayear . '-09-01'));
            return $papers;
        }
        
        
        /**
         * Returns the current accademic year.
         * 
         * @return string The current academic year
         */
        private function getacadyear()
        {
            # Checks if the month is before academic year's start (september start)
            if (intval(date('m') < 9))
            {
                $ayear = date('Y') - 1;
            }
            else
            {
                $ayear = date('Y');
            }
            
            return $ayear;
        }
        
        /**
         * Returns all the papers that have been checked - includes the checkers details
         * 
         * @return array This array has all the papers details, plus the checkers name
         */
        private function getadminpapers()
        {
            # Retrieve all the papers from checker that have been checked
            $query = R::getAll("SELECT P.*, C.* FROM upload P INNER JOIN checker C WHERE C.checked = 1 AND C.paper_id = P.id");
            $checkmap = R::convertToBeans('checkmap', $query);
            return $checkmap;
        }
        
    }

?>
