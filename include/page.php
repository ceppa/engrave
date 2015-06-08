<input type="hidden" name="activemodule" id="activemodule" />
<div id="bottom" class="container">
	<img src="img/home.png" class="home_button" alt="home" onclick="showModule('main')"/>
	<img src="img/contatti.png" class="contatti_button"  onclick="showModule('contatti')" alt="contatti"/>
	<img src="img/fbengrave.png" id="fboff" alt="facebook"/>
	<div id="fbon"></div>
	<div id="navigation">
		<img src="img/engraved.png" class="navigation_engraved" alt="engraved" />
		<img src="img/engraved1.png" class="navigation_engraved_big" alt="engraved" />
		<img src="img/graphicdesign.png" class="navigation_graphic_design" alt="engraved" />
		<img src="img/graphicdesign1.png" class="navigation_graphic_design_big" alt="graphic_design" />
		<img src="img/fashionstyle.png" class="navigation_fashion_style" alt="fashion_style" />
		<img src="img/fashionstyle1.png" class="navigation_fashion_style_big" alt="fashion_style" />
		<img src="img/digitalprint.png" class="navigation_digital_print" alt="digital_print" />
		<img src="img/digitalprint1.png" class="navigation_digital_print_big" alt="digital_print" />
		<img src="img/3dprint.png" class="navigation_3d_print" alt="3d_print" />
		<img src="img/3dprint1.png" class="navigation_3d_print_big" alt="3d_print" />
		<img src="img/furnishingaccessories.png" class="navigation_furnishing_accessories" alt="furnishing_accessories" />
		<img src="img/furnishingaccessories1.png" class="navigation_furnishing_accessories_big" alt="furnishing_accessories" />
		<img src="img/advertising.png" class="navigation_advertising" alt="advertising" />
		<img src="img/advertising1.png" class="navigation_advertising_big" alt="advertising" />
	</div>
</div>
<div id="div_main" class="container">
	<dl class="image_map">
		<dd><span class="map_engraved" title="engraved"></span></dd>
		<dd><span class="map_graphic_design" title="graphic design"></span></dd>
		<dd><span class="map_fashion_style" title="fashion style"></span></dd>
		<dd><span class="map_digital_print" title="digital print"></span></dd>
		<dd><span class="map_furnishing_accessories" title="furnishing accessories"></span></dd>
		<dd><span class="map_advertising" title="advertising"></span></dd>
		<dd><span class="map_3d_print" title="3d print"></span></dd>
	</dl>
	<div id="footer">
		P. IVA : 02698910300
	</div>
</div>
<div id="div_engraved" class="container">
	<div class="modules text_engraved" >
		<?
			include("engraved.php");		
		?>
	</div>
</div>
<div id="div_graphic_design" class="container">
	<div class="modules text_graphic_design" >
		<?
			include("graphic_design.php");		
		?>
	</div>
</div>
<div id="div_fashion_style" class="container">
	<div class="modules text_fashion_style">
		<?
			include("fashion_style.php");		
		?>
	</div>
</div>
<div id="div_digital_print" class="container">
	<div class="modules text_digital_print" >
		<?
			include("digital_print.php");		
		?>
	</div>
</div>
<div id="div_3d_print" class="container">
	<div class="modules text_3d_print">
		<?
			include("3d_print.php");		
		?>
	</div>
</div>
<div id="div_furnishing_accessories" class="container">
	<div class="modules text_furnishing_accessories" >
		<?
			include("furnishing_accessories.php");		
		?>
	</div>	
</div>
<div id="div_advertising" class="container">
	<div class="modules text_advertising" >
		<?
			include("advertising.php");		
		?>
	</div>	
</div>
<div id="div_contatti" class="container">
	<div class="text_contatti">
VIA SAN DANIELE, 49 - 33035 MARTIGNACCO (UD)<br>
E-MAIL: INFO@ENGRAVELAB.IT<br>
TEL: + 39 0432 677991 - CELL: +39 393 8275617<br>
C.F. - P.I. 02698910300<br>
FACEBOOK: <span class="button" style="z-index:1000"
onclick="openlink('http://www.facebook.com/engravelab')">
www.facebook.com/engravelab</span>
<br>
	</div>
</div>
