<?php
function generate_block_settings_classnames(){
    $block_style = $class_name = '';
    // Spacing Top and Bottom
    $spacing_top = get_field( 'spacing_top' );
    $spacing_bottom = get_field( 'spacing_bottom' );


    switch( $spacing_top ){
        case 'small':
            $class_name .= ' padding-top-sm';
            break;
        case 'medium':
            $class_name .= ' padding-top-md';
            break;
        case 'large':
            $class_name .= ' padding-top-lg';
            break;
        default:
            break;
    }

    switch( $spacing_bottom ){
        case 'small':
            $class_name .= ' padding-bottom-sm';
            break;
        case 'medium':
            $class_name .= ' padding-bottom-md';
            break;
        case 'large':
            $class_name .= ' padding-bottom-lg';
            break;
        default:
            break;
    }

    // Block Background

    $background_type = get_field( 'select_background_type' );

    if( 'color' === $background_type ){
        $block_background_color = get_field( 'block_background_color' );

        switch( $block_background_color ){
            case 'primary':
                $class_name .= ' background-primary';
                break;
            case 'secondary':
                $class_name .= ' background-secondary';
                break;
            case 'custom':
                $custom_background_color = get_field( 'custom_background_color' );
                $custom_text_color = get_field( 'custom_text_color' );
                $class_name .= ' has-custom-background';
                if($custom_background_color){
                    $block_style .= 'background-color: ' . $custom_background_color . ';';
                }
                if($custom_text_color){
                    $block_style .= 'color: ' . $custom_text_color . ';';
                }
                break;
            default:
                break;

        }

    } elseif( 'gradient' === $background_type ){
        $block_background_gradient = get_field( 'block_background_gradient' );

        switch( $block_background_gradient ){
            case 'default':
                $class_name .= ' has-background-gradient';
                break;
            default:
                break;
        }

    } elseif( 'image' === $background_type){
        if ( have_rows( 'block_background_image' ) ) :
            while ( have_rows( 'block_background_image' ) ) :
                the_row();
                $background_image = get_sub_field( 'select_background_image' );
                $background_repeat = get_sub_field( 'background_repeat' );
                $background_position = get_sub_field( 'background_position' );
                $background_size = get_sub_field( 'background_size' );

                if ( $background_image ) :
                    $block_style .= 'background-image: url(' . $background_image['url'] . ');';
                    $class_name .= ' has-background-image';
                endif;

                switch( $background_repeat ){
                    case 'norepeat':
                        $block_style .= 'background-repeat: no-repeat;';
                        break;
                    case 'repeat':
                        $block_style .= 'background-repeat: repeat;';
                        break;
                    case 'repeatx':
                        $block_style .= 'background-repeat: repeat-x;';
                        break;
                    case 'repeaty':
                        $block_style .= 'background-repeat: repeat-y;';
                        break;
                    default:
                        break;
                }
  
                switch( $background_position ){
                    case 'leftTop':
                        $block_style .= 'background-position: left top;';
                        break;
                    case 'leftCenter':
                        $block_style .= 'background-position: left center;';
                        break;
                    case 'leftBottom':
                        $block_style .= 'background-position: left bottom;';
                        break;
                    case 'centerTop':
                        $block_style .= 'background-position: center top;';
                        break;
                    case 'center':
                        $block_style .= 'background-position: center center;';
                        break;
                    case 'centerBottom':
                        $block_style .= 'background-position: center bottom;';
                        break;
                    case 'rightTop':
                        $block_style .= 'background-position: right top;';
                        break;
                    case 'rightCenter':
                        $block_style .= 'background-position: right center;';
                        break;
                    case 'rightBottom':
                        $block_style .= 'background-position: right bottom;';
                        break;
                    case 'custom':
                        $background_position_x = get_sub_field( 'background_position_x' );
                        $background_position_y = get_sub_field( 'background_position_y' );

                        $pos_x = $background_position_x ? $background_position_x : 'center';
                        $pos_y = $background_position_y ? $background_position_y : 'center';

                        $block_style .= 'background-position: ' . $pos_x .' '. $pos_y . ';';
                        break;
                    default:
                        break;
                }

                switch( $background_size ){
                    case 'auto':
                        $block_style .= '';
                        break;
                    case 'cover':
                        $block_style .= 'background-size: cover;';
                        break;
                    case 'contain':
                        $block_style .= 'background-size: contain;';
                        break;
                    case 'custom':
                        $background_size_x = get_sub_field( 'background_size_x' );
                        $background_size_y = get_sub_field( 'background_size_y' );

                        $pos_x = $background_size_x ? $background_size_x : 'center';
                        $pos_y = $background_size_y ? $background_size_y : 'center';

                        $block_style .= 'background-size: ' . $pos_x .' '. $pos_y . ';';
                        break;
                    default:
                        break;
                }
            endwhile;
        endif;
    }

    return array(
        'class_name' => $class_name,
        'block_style' => $block_style
    );
}