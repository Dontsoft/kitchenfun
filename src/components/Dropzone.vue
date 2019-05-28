<template>
    <div class="dropzone-container column padding s" ref="dropzone-container">
        <label class="dropzone-visible" for="dropzone-input">
            Hier Datei ablegen oder klicken
        </label>
        <div class="file-display " v-if="file != null">
            {{ file.name }}
        </div>
        <div v-if="file == null"></div>
        <input type="file" accept=".csv" id="dropzone-input" ref="dropzone-input"/>
    </div>
</template>

<script>

export default {
    data: () => {
        return {
            file: null
        }
    },
    methods: {
        upload: function(f) {
            this.$emit('handle', f);
        },
        show_error: function(e) {
            this.$emit('error', e);
        }
    },
    mounted() {
        var _this = this;
        ['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach((event) => {
            _this.$refs['dropzone-container'].addEventListener(event, (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.dataTransfer.dropEffect = 'copy';
            });
        });
        ['dragover', 'dragenter'].forEach((event) => {
            var e = _this.$refs['dropzone-container'];
            e.addEventListener(event, (_e) => {
                e.classList.add('dropping');
            });
        });
        ['dragleave', 'dragend', 'drop'].forEach((event) => {
            var e = _this.$refs['dropzone-container'];
            e.addEventListener(event, (_e) => {
                e.classList.remove('dropping');
            });
        });
        this.$refs['dropzone-container'].addEventListener('drop', (e) => {
            if (e.dataTransfer) {
                _this.upload(e.dataTransfer.files[0]);
            } else if (e.originalEvent) {
                _this.upload(e.originalEvent.dataTransfer.files[0]);
            } else {
                _this.show_error('Unable to get dropped file. Please try uploading by clicking.')
            }
        });
        this.$refs['dropzone-input'].addEventListener('change', (e) => {
            _this.upload(_this.$refs['dropzone-input'].files[0]);
        });
    }
}
</script>


<style lang="scss" scoped>
@import './../assets/_variables.scss';
    .dropzone-container {
        width: 100%;
        height: 100%;
        & .dropzone-visible {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            position: relative;
            border-radius: 8px;
            color: $primary;
            &:hover {
                background: rgba($primary, 0.1);
                cursor: pointer;
            }
            &::after {
                display: block;
                content: '';
                position: absolute;
                width: 100%;
                height: 100%;
                left: 0;
                top: 0;
                border: 8px dashed rgba($primary, 0.4);
                border-radius: 8px;
            }
        }
        &.dropping {
            & .dropzone-visible {
                background: rgba($primary, 0.1);
                cursor: pointer;
            }
        }

        & .file-display {
            width: 100%;
            padding-top: $height-xs;
        }
    }

    #dropzone-input {
        display: none;
    }
</style>
