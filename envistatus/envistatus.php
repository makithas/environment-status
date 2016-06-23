<!DOCTYPE html>
<html lang="en">
   <head>
      <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
      <link rel="stylesheet" href="css/style.min.css">
      <link rel='stylesheet' type='text/css' href='css/bootstrap.css' />
      <link rel='stylesheet' type='text/css' href='css/pulse-css.css' />
      <script src="js/login.js"></script> 
      <header class="Header">
         <a href="" class="Header-logo"><img src="/moodle/DynamicPage/images/pearson-logo-dark.svg" onerror="this.src='/moodle/DynamicPage/images/pearson-logo-dark.png'; this.onerror=null;" alt="Pearson Pulse"></a>
         <div class="container">
            <form class="form-inline" role="form">
               <div class="form-group">
                  <label for="username">Username:</label>
                  <input type="username" class="form-control" id="username" placeholder="Enter Username">
               </div>
               <div class="form-group">
                  <label for="password">Password:</label>
                  <input type="password" class="form-control" id="password" placeholder="Enter password">
               </div>
               <button type="submit" class="btn btn-default" onclick="validate()">Submit</button> 
            </form>
         </div>
      </header>
   </head>
   <body>
      <div class="o-section">
         <div id="tabs" class="c-tabs no-js">
            <div class="c-tabs-nav">
               <a href="#" class="c-tabs-nav__link is-active">
               <span>Development</span>
               </a>
               <a href="#" class="c-tabs-nav__link">
               <span>Test</span>
               </a>
               <a href="#" class="c-tabs-nav__link">
               <span>Staging</span>
               </a>
               <a href="#" class="c-tabs-nav__link">
               <span>Production</span>
               </a>
            </div>
            <?php
               function content()
               {
                   $userid    = 'padmin';
                   $password  = 'secret12';
                   $url       = 'http://auth.dev.pulse.pearson.com';
                   $ch        = curl_init();
                   $agent     = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
                   $headers   = array();
                   $headers[] = "Content-Type: application/json";
                   $headers[] = "Accept: application/json";
                   $headers[] = "Appversion: 2";
                   $headers[] = "Deviceid: 1";
                   $headers[] = "Accept-Language: en-US;q=0.6,en;q=0.4";
                   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                   curl_setopt($ch, CURLOPT_URL, "$url/user/authenticate");
                   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                   curl_setopt($ch, CURLOPT_USERAGENT, $agent);
                   curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"username\": \"$userid\",\n  \"password\": \"$password\"\n}");
                   curl_setopt($ch, CURLOPT_POST, 1);
                   $result  = curl_exec($ch);
                   $out1    = json_decode($result);
                   $mytoken = $out1->access_token;
                   $url2    = $url . "/user/isTokenValid";
                   $resturl = sprintf("%s?%s", $url2, http_build_query(array(
                       "id" => $userid
                   )));
                   curl_setopt_array($ch, array(
                       CURLOPT_URL => $resturl,
                       CURLOPT_RETURNTRANSFER => true,
                       CURLOPT_USERAGENT => $agent,
                       CURLOPT_MAXREDIRS => 5,
                       CURLOPT_TIMEOUT => 30,
                       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                       CURLOPT_CUSTOMREQUEST => "GET",
                       CURLOPT_SSL_VERIFYHOST => 0,
                       CURLOPT_SSL_VERIFYPEER => false,
                       CURLOPT_HTTPHEADER => array(
                           "appVersion: 2",
                           "deviceId: 1",
                           "Authorization: Bearer $mytoken",
                           "cache-control: no-cache",
                           "Accept-Language: en-US;q=0.6,en;q=0.4"
                       )
                   ));
                   $out2 = curl_exec($ch);
                   if ($out2 == true) {
                       
                   } else {
                       echo 'Your token is not valid';
                   }
                   $url3      = $url . "/school";
                   $headers   = array();
                   $headers[] = "Content-Type: application/json";
                   $headers[] = "Accept: application/json";
                   $headers[] = "Deviceid: 1";
                   $headers[] = "Appversion: 2";
                   $headers[] = "Authorization: Bearer $mytoken";
                   $headers[] = "Accept-Language: en-US;q=0.6,en;q=0.4";
                   curl_setopt($ch, CURLOPT_URL, $url3);
                   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                   curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                   curl_setopt($ch, CURLOPT_USERAGENT, $agent);
                   $result     = curl_exec($ch);
                   $out3       = json_decode($result);
                   $school_ids = array();
                   for ($i = 0; $i < count($out3); $i++) {
                       //$getWStoken = $out3[$i]->links->wstoken;
                       $test         = @preg_match('/moodle\/([0-9a-z]+)\//', $out3[$i]->links->moodle, $match);
                       $school_ids[] = (@$match[1]);
                   }
                   //Remove duplicate and reindex
                   $unique_schools = array_values(array_filter(array_unique($school_ids)));
                   $i              = 1;
                   foreach ($unique_schools as $val) {
                       echo 'School' . $i++ . '<br>' . $val . '&nbsp;&nbsp;&nbsp;';
                       $url4 = "http://www.dev.pulse.pearson.com/moodle/$val/webservice/rest/server.php?wsfunction=pulse_health_check&wstoken=d204fed1145aaccf07812b89918aa284&source=pulse&moodlewsrestformat=json";
                       curl_setopt($ch, CURLOPT_URL, "$url4");
                       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                       curl_setopt($ch, CURLOPT_USERAGENT, $agent);
                       curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"username\": \"$userid\",\n  \"password\": \"$password\"\n}");
                       curl_setopt($ch, CURLOPT_POST, 1);
                       $out4 = curl_exec($ch);
                       print_r($out4);
                       echo '<br>';
                   }
                   curl_close($ch);
               }
               ?>
            <div class="c-tab is-active">
               <div class="c-tab__content">
                  <h2>Development</h2>
                  <?php
                     content();
                     ?>
               </div>
            </div>
            <div class="c-tab">
               <div class="c-tab__content">
                  <h2>Test</h2>
                  <?php
                     content();
                     ?>
               </div>
            </div>
            <div class="c-tab">
               <div class="c-tab__content">
                  <h2>Staging</h2>
                  <?php
                     content();
                     ?>
               </div>
            </div>
            <div class="c-tab">
               <div class="c-tab__content">
                  <h2>Production</h2>
                  <?php
                     content();
                     ?>
               </div>
            </div>
         </div>
      </div>
   </body>
   <div class="Footer">
   <div class="Footer-main Grid">
      <a href="" class="Footer-logo"><img src="/moodle/DynamicPage/images/pearson-logo-dark.svg" onerror="this.src='/moodle/DynamicPage/images/pearson-logo-dark.png'; this.onerror=null;" alt="Pearson Pulse"></a>
      <ul class="Footer-list">
         <li class="Footer-listItem"><a href="">Terms &amp; Conditions</a></li>
         <li class="Footer-listItem"><a href="">Cookie Policy</a></li>
         <li class="Footer-listItem"><a href="">Privacy Policy</a></li>
      </ul>
   </div>
   <div class="c-brandBold">
      <div class="Footer-foot Grid">
         <div class="t-small">
            <p style='align:right'>&copy; Pearson Education</p>
         </div>
      </div>
   </div>
   <script src="js/tabs.js"></script>
   <script>
      var myTabs = tabs({
        el: '#tabs',
        tabNavigationLinks: '.c-tabs-nav__link',
        tabContentContainers: '.c-tab'
      });
      myTabs.init();
   </script>
   </body>
</html>
