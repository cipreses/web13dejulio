<el-tab-pane class="bpa-tabs--v_ls__tab-item--pane-body" name ="integration_settings" label="integration" data-tab_name="integration_settings">	
	<span slot="label">
		<i class="material-icons-round">extension</i>
		<?php esc_html_e( 'Integrations', 'bookingpress-appointment-booking' ); ?>
	</span>	
	<div class="bpa-general-settings-tabs--pb__card">		
		<div class="bpa-gs--tabs-pb__content-body">					
			<div class="bpa-cmc--tab-menu bpa-gs--integrations-tab-menu">    				                            
                <div class="bpa-cms-tm__body">
                    <el-radio-group v-model="bpa_integration_active_tab" ref="integration_setting_radios" v-for="tab_item in bookingpress_tab_list" @change="integration_tab_select($event)">
                        <el-radio-button :label="tab_item.tab_value">{{tab_item.tab_name}}</el-radio-button>
					</el-radio-group>
                </div>
            </div>						
			<?php
			do_action('bookingpress_add_integration_settings_section');			
			?>
		</div>	
	</div>
</el-tab-pane>



