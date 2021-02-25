<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />
    <link
      rel="shortcut icon"
      href="images/comtech-96x96.png"
      type="image/x-icon"
    />
    <title>AFTC Meetings</title>
    <!-- NC State Bootstrap CSS -->
    <link
      href="https://cdn.ncsu.edu/brand-assets/bootstrap-4/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <!-- JS for Billboard dynamic Slides -->
      <script src="https://billboard.ncsu.edu/js/jquery.js"></script>
      <script src="https://billboard.ncsu.edu/js/slideEvents.js"></script>
      <script src="https://billboard.ncsu.edu/js/scrollingText.js"></script>
    <style>
      .red-block {
	background-color: #cc0000;
      }
      #comtechLogo {
        background-color: white;
	padding: 5px;
      }
      #logo {
	padding-top: 20px;
	padding-bottom: 20px;
	margin-left: 25px;
      }
      #title h1{
	font-size: 100px;
      }
      #date time{
	font-size: 60px;
	text-align: left;
	position: relative;
	left: 10px;
	top: 10px;
      }
      h3 {
			font-size: 80px;
	}
	#startTime {
			border-right-style: solid;
			border-width: 10px;
			border-color: #cc0000;
	}
	#roomName {
		position: absolute;
		right: 3rem;
			text-align: right;
	}
    </style>
  </head>
  <body onload="showGroups()">
   <div class="container-fluid page-header text-white red-block sticky-top">
	<div class="row align-items-center">
		<div id="logo" class="col-2">
        	<img
            		src="images\comtech_Logo.png"
            		alt="ComTech Logo"
            		id="comtechLogo"
			width=250
			height=250
          	/>
		</div>
		<div id="title" class="col-5 text-center">
          		<h1>AFTC Meetings</h1>
		</div>
		<div id="date" class="col text-center">
          		<time><?php echo date("l F jS, Y"); ?></time>
		</div>
	</div>
  </div>
		<!-- How to auto-scroll when a lot of events? -->
      <main>
		  <div class="container-fluid scrollingText">
			  <?php include "AFH_CalendarsEvents.php";?>
		  </div>
      </main>

      <footer>
        <address>
          TESTING VERSION<br />
          Contact <a href="dpsarso2@ncsu.edu">dpsarso2@ncsu.edu</a><br />
        </address>
      </footer>
      
      <script>
		  //Borrowed slideshow code to show 4 events at a time.
		  //idk how to use NCSU billboard.ncsu.edu code, so ¯\_(ツ)_/¯
		var eventGroupsIndex = 0; 
		function showGroups() {
			var groups = document.getElementsByClassName("eventGroup");
			var groupsLength = groups.length;
			console.log("Group Length" + groupsLength);
			for(let i = 0; i < groupsLength; i++) {
				groups[i].style.display = "none";
			}
			if(eventGroupsIndex >= groupsLength) { eventGroupsIndex = 0 };
			console.log(eventGroupsIndex);
			groups[eventGroupsIndex++].style.display="block";
			setTimeout(showGroups, 4000);	//change page every 4 seconds.
		}
      </script>
  </body>
</html>
