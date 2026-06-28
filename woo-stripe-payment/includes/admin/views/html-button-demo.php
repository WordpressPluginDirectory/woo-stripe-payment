<?php
defined( 'ABSPATH' ) || exit;
?>
<tr valign="top">
    <th scope="row" class="titledesc"><label
                for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?><?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok. ?></label>
    </th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php echo wp_kses_post( $data['title'] ); ?></span>
            </legend>
            <label for="<?php echo esc_attr( $field_key ); ?>">
                <div id="<?php echo esc_attr( $data['id'] ); ?>"></div>
				<?php if ( ! empty( $data['contexts'] ) ) : ?>
                    <select id="<?php echo esc_attr( $data['id'] ); ?>-context" style="margin-top:8px;">
						<?php foreach ( $data['contexts'] as $value => $label ) : ?>
                            <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
                    </select>
				<?php endif; ?>
				<?php echo $this->get_description_html( $data ); // WPCS: XSS ok. ?>
        </fieldset>
    </td>
</tr>
