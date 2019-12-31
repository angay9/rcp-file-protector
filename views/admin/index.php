<script>
    window.RcpFileProtector = {
        memberships: <?php echo json_encode($memberships); ?>,
        protectionLevels: <?php echo json_encode($protectionLevels); ?>,
        errors: <?php echo (count($errors) > 0 ? json_encode($errors) : "null"); ?>,
        successMessage: "<?php echo $successMessage; ?>"
    };
</script>

<div class="wrap">
    <div id="rcp-file-protector-options" v-cloak>
        <h1>
            RCP File Protector Settings
        </h1>

        <div>
            <h3>
                Protection Levels 
            </h3>
                        
            <div v-if="successMessage" class="rcp-fp-notice rcp-fp-notice--success">
                {{ successMessage }}
            </div>
            
            <div class="rcp-fp-notice rcp-fp-notice--info">
                Select one or multiple memberships that will have access to a given URL
            </div>

            <div v-if="errors.count() > 0" class="rcp-fp-notice rcp-fp-notice--danger">
                <p v-for="(fieldErrors, key) in errors.all()" v-html="fieldErrors.join('<br>')">
                </p>
            </div>

            <div class="rcp-fp-form-row">
                <button class="button button-primary" @click.prevent="addProtectionLevel">Add New</button>
            </div>

            <form id="rcp-options-form" @submit="onSubmit" method="POST">
                
                <?php wp_nonce_field('rcp-file-protector_save-options', 'rcp-file-protector_nonce'); ?>

                <div v-if="protectionLevels.length > 0" >
                    <div v-for="(level, index) in protectionLevels" class="rcp-fp-protection-level">
                        <div class="rcp-fp-form-row">
                            <label class="rcp-fp-protection-level__label">Membership</label>

                            <selectize :name="'levels[' + index + '][memberships][]'" v-model="level.memberships" :settings="membershipSelectSettings">
                                <option :value="membership.id" v-for="membership in memberships">{{ membership.name }}</option>
                            </selectize>
                        </div>
                        
                        <div class="rcp-fp-form-row">
                            <label class="rcp-fp-protection-level__label">Allow Access To URL</label>
                            <div :class="{'rcp-fp-form-group': !level.isRegex}">
                                <span v-show="!level.isRegex" class="rcp-fp-protection-level__addon">/wp-content/uploads/</span>
                                <input :name="'levels[' + index + '][url]'" v-model="level.url" type="text" class="rcp-fp-protection-level__input">
                            </div>
                        </div>

                        <div class="rcp-fp-form-row">
                            <label class="rcp-fp-protection-level__label">
                                <input type="checkbox" :name="'levels[' + index + '][isRegex]'" v-model="level.isRegex"> Use Regular Expression
                            </label>
                        </div>
                        
                        <div class="rcp-fp-form-row align-right">
                            <button class="button rcp-fp-btn-danger" @click.prevent="removeProtectionLevel(index)">Remove</button>
                        </div>
                    </div>

                </div>

                <div class="rcp-fp-notice rcp-fp-notice--info" v-else>
                    <p>
                        No protection levels yet.
                    </p>
                </div>

                <div class="rcp-fp-form-row">
                    <button class="button button-primary" type="submit">Save</button>
                </div>

            </form>
        </div>
    </div>
</div>
