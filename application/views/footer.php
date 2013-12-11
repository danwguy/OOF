<?php
//	$bm->add('total_execution_time_end');
	?>
</div>
<div class='theme-container'>
    <div class='pull-arrow'>
        <p class='pull-arrow-p' title='Theme Chooser'>></p>
    </div>
    <div class='theme-holder'>
        <div class='theme-chooser'>
            <label for='themes'>
                Theme:
                <select name='themes' id='themes' class='styled-select'>
                    <option value=''>--SELECT A THEME--</option>
                    <?php
                        if(isset($themes)) {
                            foreach($themes as $theme) {
                                echo "<option value='".$theme->css_class."' title='".$theme->description."'>".$theme->title."</option>";
                            }
                        }
                    ?>
                </select>
            </label>
        </div>
    </div>
</div>
</div>
<div class='footer'>
    <p class='copy'>Copyright &copy; 2013 Robert Mason</p>
    <p class='designed'>Designed and built by Robert Mason</p>
	<p class="time_and_memory">Page Loaded in: {time_elapsed} seconds and used {memory_usage} of RAM</p>
</div>
</div>

</body>
</html>