<nav id="sidebar" class="sidebar js-sidebar">
			<div class="sidebar-content js-simplebar">
				<a class="sidebar-brand" href="index.html">
          <span class="align-middle">Admin Control panel</span>
        
		
			</a>

				<ul class="sidebar-nav">
					

					<li class="sidebar-item <?=($p=="dashboard"?'active':'')?>">
						<a class="sidebar-link" href="index.php">
                        <i class="align-middle" data-feather="sliders"></i> <span class="align-middle"> Admin Dashboard</span>
            </a>
					</li>

                    <li class="sidebar-item <?=($p=="slideshow"?'active':'')?>"">
						<a class="sidebar-link" href="index.php?p=slideshow">
                        <i class="align-middle" data-feather="archive"></i> <span class="align-middle">Banner</span>
            </a>
					</li>
                    <li class="sidebar-item <?=($p=="product"?'active':'')?>"">
						<a class="sidebar-link" href="index.php?p=product">
                        <i class="align-middle" data-feather="box"></i> <span class="align-middle">Products</span>
            			</a>
					</li>
					<li class="sidebar-item <?=($p=="user"?'active':'')?>"">
						<a class="sidebar-link" href="index.php?p=user">
                        <i class="align-middle" data-feather="box"></i> <span class="align-middle">User</span>
            			</a>
					</li>
					<li class="sidebar-item <?=($p=="order"?'active':'')?>"">
						<a class="sidebar-link" href="index.php?p=order">
                        <i class="align-middle" data-feather="box"></i> <span class="align-middle">Order</span>
            			</a>
					</li>

				</ul>
			</div>
		</nav>
		  