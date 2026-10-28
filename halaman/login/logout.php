<?php
supabase_logout();
session_start();
redirect('login');