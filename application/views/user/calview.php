<style type="text/css">
	table{
border: 15px solid #25BAE4;
border-collapse:collapse;
margin-top: 50px;
margin-left: 250px;
}
td{
width: 50px;
height: 50px;
text-align: center;
border: 1px solid #e2e0e0;
font-size: 18px;
font-weight: bold;
}
th{
height: 50px;
padding-bottom: 8px;
background:#25BAE4;
font-size: 20px;
}
.prev_sign a, .next_sign a{
color:white;
text-decoration: none;
}
tr.week_name{
font-size: 16px;
font-weight:400;
color:red;
width: 10px;
background-color: #efe8e8;
}
.highlight{
background-color:#25BAE4;
color:white;
height: 27px;
padding-top: 13px;
padding-bottom: 7px;
}
.calender .days td
{
	width: 80px;
	height: 80px;
}
.calender .hightlight
{
	font-weight: 600px;
}
.calender .days td:hover
{
	background-color: #DEF;
}
</style>
	<div id="body">
		<?php echo $calender; ?>
	</div>	