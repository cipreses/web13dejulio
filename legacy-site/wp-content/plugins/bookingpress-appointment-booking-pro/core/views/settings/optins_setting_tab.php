<el-tab-pane class="bpa-tabs--v_ls__tab-item--pane-body" name ="optin_settings" label="optin" data-tab_name="optin_settings">	
	<span slot="label">
		<i class="material-icons-round">settings_suggest</i>
		<?php esc_html_e( 'Opt-ins', 'bookingpress-appointment-booking' ); ?>
	</span>	
	<div class="bpa-general-settings-tabs--pb__card">		    
		<div class="bpa-gs--tabs-pb__content-body">	
			<div class="bpa-cmc--tab-menu bpa-gs--integrations-tab-menu">    				                            
                <div class="bpa-cms-tm__body">
                    <el-radio-group v-model="bpa_optin_active_tab" ref="optins_setting_radios" v-for="tab_item in bookingpress_optin_tab_list" @change="optins_tab_select($event)">                       
                        <el-radio-button :label="tab_item.tab_value">{{tab_item.tab_name}}</el-radio-button>
					</el-radio-group>
                </div>
            </div>
			<?php
			do_action('bookingpress_add_optin_settings_section');
			?>
		</div>	
	</div>
</el-tab-pane>
