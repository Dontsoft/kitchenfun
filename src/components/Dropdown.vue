<template>
    <div class="dropdown" ref="container">
        <div class="current-item item" @click="toggleDropdown">
            <span class="label">{{currentElement ? currentElement.label : 'Nothing selected'}}</span>
        </div>
        <div class="dropdown-content column" v-if="showDropdown">
            <div class="item not-selected" v-for="e in filtered_elements" v-bind:key="e.label" @click="updateElement(e)">{{ e.label }}</div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    @import './../assets/_variables.scss';
    .dropdown {
        width: 100%;
        position: relative;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
        border: 1px solid rgba($color: $dark, $alpha: 0.1);
        .current-item {
            position: relative;
            padding-right: $height-m;
            &::after {
                content: '';
                position: absolute;
                right: 0;
                top: 50%;
                height: $height-m;
                width: $height-m;
                transform: translateY(-$height-s);
                background-position: center;
                background-size: $height-m;
                background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj48cGF0aCBkPSJNMTI4IDE5MmwxMjggMTI4IDEyOC0xMjh6Ii8+PC9zdmc+);
            }
        }
        &.show {
            box-shadow: 0px 1px 1px rgba($color: $dark, $alpha: 0.2);
            border: 1px solid rgba($color: $dark, $alpha: 0.4);
            .current-item::after {
                transform-origin: 50% 25%;
                transform: rotate(-180deg);
            }

            .dropdown-content {
                background-color: $light;
                box-shadow: 0px 1px 1px rgba($color: $dark, $alpha: 0.2);
                position: absolute;
                z-index: 3000;
                top: 18.5;
                left: 0;
                width: 100%;
                min-height: $height-xl;
            }
        }
    }
    .item {
        padding: $height-xs;
        cursor: pointer;
        &.not-selected {
            &:hover {
                background-color: $primary;
                color: $light;
            }
        }
    }

    /**/
</style>

<script>
export default {
    props: {
        elements: {
            type: Array,
            default: []
        }
    },
    data: () => {
        return {
            currentElement: {
                label: "Nothing selected"
            },
            showDropdown: false
        }
    },
    computed: {
        filtered_elements: function() {
            var _this = this;
            return this.elements.filter((e) => {
                return e.label != _this.currentElement.label;
            });
        }
    },
    methods: {
        toggleDropdown: function() {
            this.showDropdown = !this.showDropdown;
            if (this.showDropdown) {
                this.$refs['container'].classList.add('show');
            } else {
                this.$refs['container'].classList.remove('show');
            }
        },
        updateElement: function(element) {
            this.currentElement = element;
            this.showDropdown = false;
            this.$refs['container'].classList.remove('show');
            this.$emit('changed', element);
        }
    },
    mounted() {
        if (this.elements.length > 0) {
            this.updateElement(this.elements[0]);
        }
        if (this.showDropdown) {
            this.$refs['container'].classList.add('show');
        } else {
            this.$refs['container'].classList.remove('show');
        }
    }
}
</script>