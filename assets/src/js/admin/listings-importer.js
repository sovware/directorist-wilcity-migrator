document.addEventListener( 'DOMContentLoaded', init, false );

const $ = jQuery;
function Tasks() {
    return {
        init: function() {
            this.handleListingsImportSourceNavLink();
            this.handleDirectoryTypeSwitching();
            this.handleListingImport();
        },


        handleListingImport: function() {
            const stepTwo = $('#directorist_wilcity_migrator');

            $( stepTwo ).on('submit', function (e) {
                e.preventDefault();
        
                $('.atbdp-importer-mapping-table-wrapper').fadeOut(300);
                $('.directorist-importer__importing').fadeIn(300);
                $(this)
                    .parent('.csv-fields')
                    .fadeOut(300);
                $('.atbdp-mapping-step')
                    .removeClass('active')
                    .addClass('done');
                $('.atbdp-progress-step').addClass('active');
        
                let position = 0;
                let failed   = 0;
                let imported = 0;
        
                const configFields = $( '.directorist-listings-importer-config-field' );
        
                let counter = 0;
                var run_import = function () {
                    const form_data = new FormData();
        
                    // ajax action
                    form_data.append( 'action', 'directorist_import_listing' );
                    form_data.append( 'position', position );
                
                    // Get Config Fields Value
                    if ( configFields.length ) {
                        configFields.each( ( index, item ) => {
                            const key   = $( item ).attr( 'name' );
                            const value = $( item ).val();
        
                            form_data.append( key, value );
                        });
                    }
        
                    var map_elm = null;
        
                    if ( $('select.atbdp_map_to').length ) {
                        map_elm = $('select.atbdp_map_to');
                    }
        
                    if ( $('input.atbdp_map_to').length ) {
                        map_elm = $('input.atbdp_map_to');
                    }
        
                    var directory_type = $( '#directory_type' ).val();
                    if( directory_type ) {
                        form_data.append( 'directory_type', directory_type );
                    }
                    
                    if ( map_elm ) {
                        var log = [];
                        map_elm.each( function () {
                            const name  = $(this).attr('name');
                            const value = $(this).val();
        
                            const postFields = [
                                'listing_status',
                                'listing_title',
                                'listing_content',
                                'listing_img',
                                'directory_type',
                            ];
        
                            const taxonomyFields = [
                                'category',
                                'location',
                                'tag',
                            ];
        
                            if (  postFields.includes( value ) ) {
                                form_data.append( value, name );
                                log.push( { [ value ]: name } );
                            } else if ( taxonomyFields.includes( value ) ) {
                                form_data.append( `tax_input[${value}]`, name );
                                log.push( { [ `tax_input[${value}]` ]: name } );
                            } else if ( value != '' ) {
                                form_data.append( `meta[${value}]`, name );
                                log.push( { [ `meta[${value}]` ]: name } );
                            }
                        });
        
                    }
        
                    $.ajax({
                        method: 'POST',
                        processData: false,
                        contentType: false,
                        // async: false,
                        url: directorist_admin.ajaxurl,
                        data: form_data,
                        success( response ) {

                            console.log( response );
                            return;
        
                            if ( response.error ) {
                                console.log({ response });
                                return;
                            }
        
                            imported += response.imported;
                            failed += response.failed;
        
                            $('.importer-details').html(
                                `Imported ${response.next_position} out of ${response.total}`
                            );
        
                            $('.directorist-importer-progress').val( response.percentage );
        
                            if ( response.percentage != '100' ) {
                                position = response.next_position;
                                run_import();
                                counter++;
                            } else {
                                window.location = `${response.url}&listing-imported=${imported}&listing-failed=${failed}`;
                            }
        
                            $('.directorist-importer-length').css( 'width', response.percentage + '%' );
                        },
        
                        error(response) {
                            window.console.log(response);
                        },
                    });
        
                };
        
                run_import();
            });
        },


        handleDirectoryTypeSwitching: function() {
            $('body').on('change', '#directorist_migrator_directory_type', function () {
                data_mapping($(this).val());
            });
        
            function data_mapping(directory_type) {
                $.ajax({
                    type: 'post',
                    url: directorist_wilcity.ajaxurl,
                    data: {
                        action: 'directorist_to_wilcity_mapping',
                        directory_type: directory_type,
                    },
                    success(response) {

                        $('.directorist_data_mapping').empty().html(response.response);
                        console.log( response );
                        return;
        
                        if ( response.error ) {
                            console.log({ response });
                            return;
                        }
        
                        $('.atbdp-importer-mapping-table').remove();
                        $('.directory_type_wrapper').after(response);
                    },
                    complete: function () {
                        $('#directorist-type-preloader').hide();
                    }
                });
            }
        },

        handleListingsImportSourceNavLink: function() {
            const listingsImportSourceNavLinks = document.querySelectorAll( '.directorist-listings-import-nav-link' );

            if ( ! listingsImportSourceNavLinks ) {
                return;
            }

            [ ...listingsImportSourceNavLinks ].forEach( item => {
                item.addEventListener( 'click', function() {
                    const query_var_value = this.getAttribute( 'data-query-var-value' );

                    // Upldate Listing import source type
                    const listing_import_source_type = document.querySelector( '.listing-import-source-type' );
                    if ( listing_import_source_type ) {
                        listing_import_source_type.setAttribute( 'value', query_var_value );
                    }

                    // Update File Upload Required Status
                    const file_upload_field = document.querySelector( '#upload' );

                    if ( file_upload_field ) {
                        if ( 'other' === query_var_value ) {
                            file_upload_field.removeAttribute( 'required' );
                        } else {
                            file_upload_field.setAttribute( 'required', true );
                        }
                    }
                });
            });
        },
    }
}

function init() {
    const tasks = new Tasks();
    tasks.init();
}

