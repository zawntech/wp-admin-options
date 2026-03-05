<?php
namespace Zawntech\WPAdminOptions;

class AttachmentOption extends AbstractAdminOption
{
    protected function render_attachment_content() {
        $key = esc_attr( $this->args['key'] );
        $description = trim( $this->args['description'] );
        $multiple = $this->args['multiple'];
        ?>

        <div class="wao-action-group">
            <button type="button" class="button" @click="openFrame()">Select Media</button>
            <button type="button" class="button" @click="clear()">Clear</button>
        </div>

        <hr>

        <p v-if="!ids.length">
            No <?= $multiple ? 'attachments are' : 'attachment is'; ?> assigned.
        </p>

        <div v-if="ids.length">

            <div v-for="(item, i) in media" class="wao-attachment-item"
                 <?php if ( $multiple ) : ?>
                 :class="{'wao-dragging': dragIndex === i, 'wao-dragover': dragOverIndex === i}"
                 draggable="true"
                 @dragstart="dragStart(i, $event)"
                 @dragover.prevent="dragOver(i)"
                 @drop="drop(i)"
                 @dragend="dragEnd"
                 <?php endif; ?>>

                <?php if ( $multiple ) : ?>
                <div class="wao-item-row">
                    <span class="wao-drag-handle" title="Drag to reorder">&#x2630;</span>
                    <div class="wao-controls">
                        <button type="button" class="button" :disabled="!canMoveUp(item.id)" @click="moveUp(item.id)">&#x25B2;</button>
                        <button type="button" class="button" :disabled="!canMoveDown(item.id)" @click="moveDown(item.id)">&#x25BC;</button>
                        <button type="button" class="button" @click="removeItem(item.id)">&times;</button>
                    </div>
                </div>
                <?php endif; ?>

                <div v-if="'Image' == getType(item)">
                    <img :src="item.url">
                </div>

                <div v-if="'Video' == getType(item)">
                    <video controls>
                        <source :src="item.url">
                    </video>
                </div>

                <div v-if="'Other' == getType(item)">
                    <a :href="item.url" target="_blank">{{ item.title }}</a>
                </div>

                <span class="wao-type-badge">
                    <a :href="'<?= admin_url( 'upload.php?item=' ); ?>' + item.id" target="_blank">
                        {{ getType(item) }}
                    </a>
                </span>

            </div>

        </div>

        <input type="hidden" :value="json" name="<?= $key; ?>">

        <?php
        if ( !empty( $description ) ) {
            printf( '<p><code>%s</code></p>', $description );
        }
    }

    public function render_taxonomy_field() {
        if ( $this->render_array_error() ) return;
        wp_enqueue_media();
        $key = esc_attr( $this->args['key'] );
        ?>
        <div class="form-field term-slug-wrap">
            <?php $this->render_option_label(false); ?>
            <div id="<?= $key; ?>">
                <?php $this->render_attachment_content(); ?>
            </div>
        </div>
        <?php
        add_action( 'admin_footer', [$this, 'render_style'] );
        add_action( 'admin_footer', [$this, 'render_script'] );
    }

    public function render_admin_table() {
        if ( $this->render_array_error() ) return;
        wp_enqueue_media();
        $key = esc_attr( $this->args['key'] );
        ?>
        <tr id="<?= $key; ?>">
            <?php $this->render_option_label(); ?>
            <td>
                <?php $this->render_attachment_content(); ?>
            </td>
        </tr>
        <?php
        add_action( 'admin_footer', [$this, 'render_style'] );
        add_action( 'admin_footer', [$this, 'render_script'] );
    }

    public function render_script() {
        $key = esc_attr( $this->args['key'] );
        $multiple = (bool) $this->args['multiple'];
        $media_types = $this->args['media_types'];

        $data = [];
        $ids = $this->args['value'];
        $ids = array_map( function( $id ) {
            return (int) $id;
        }, $ids );
        if ( is_array( $ids ) ) {
            foreach( $ids as $id ) {
                $data[] = wp_prepare_attachment_for_js( $id );
            }
        }

        ?>
        <script>window.addEventListener('load', function() {
            <?php $args = [ 'key' => $key, 'ids' => $ids, 'data' => $data, 'multiple' => $multiple, 'mediaTypes' => $media_types, 'label' => $this->args['label'] ]; ?>
            WPAdminOptions.AttachmentOption(<?= json_encode( $args ); ?>);
        });</script>
        <?php
    }

    public function render_style() {
        $color = $this->args['bg_color'];
        if ( '#FFFFFF' !== strtoupper( $color ) ) : ?>
        <style>
            #<?= esc_attr( $this->args['key'] ); ?> .wao-attachment-item {
                background-color: <?= esc_attr( $color ); ?>;
            }
        </style>
        <?php endif;
    }
}
