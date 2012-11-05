<div id="sidebar" style="width:17%">
            <ul style="height:350px">
                <li>
                <h3><a href="#" class="house">Dashboard</a></h3>
                <ul>
                <?php if($current_nav=="index"){ ?><li><a href="index.php?op=add" class="report">添加事件</a></li><?php } ?>
                <?php if($current_nav=="cost"){ ?><li><a href="cost.php?op=add" class="report">添加成本</a></li><?php } ?>
                    <?php if(isset($_SESSION['user']) && $_SESSION['user']===true){ ?><li><a href="logout.php" class="report">Logout</a></li><?php } ?>
                </ul>
                
                </li>
            </ul>
</div><!-- sidebar end -->
