<template>
    <div v-if="selectedItem" class="table-form">

        <div class="list-panel">

            <div class="table-section">
                <div class="title-header">
                    Table Configuration
                </div>
                <div class="toolbar">
                    <a class="btn btn-default" title="Back" v-if="showBackButton">
                        <i class="fa fa-chevron-left" v-on:click.stop="goBack()"></i>
                    </a>
                    <a class="btn btn-primary" title="Edit" v-on:click.stop="goEditPage()">
                        <i class="fa fa-pencil"></i>
                    </a>

                </div>
                <div class="builder-form">
                    <div class="form-group input-title">
                        <label for="table_name">Table Name</label>
                        <div class="form-control">{{selectedItem.table_name}}</div>
                    </div>
                </div>
                <table class="table table-striped table-responsive">
                    <thead>
                    <tr>
                        <th class="tbl-status"></th>
                        <th>Column</th>
                        <th>Type</th>
                        <th>Length</th>
                        <th>Nullable</th>
                        <th>PK</th>
                        <th>Default</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="selectedItem.hasEnumColumns">
                        <td colspan="9" class="text-warning warning">
                            <p><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Warning: {{ EnumWarningMsg }}</p>
                        </td>
                    </tr>
                    <tr v-for="col in selectedItem.columns" v-bind:class="{ 'drop-column': col.drop }">
                        <td v-bind:class="{migrated: col.migrated == true, 'not-migrated': col.migrated == false}" class="tbl-status"></td>
                        <td>{{ col.attributes.name }}</td>
                        <td>{{ col.attributes.type }}</td>
                        <td>{{ col.attributes.length }}</td>
                        <td><input type="checkbox" disabled="disabled" v-model='col.attributes.nullable' /></td>
                        <td><input type="checkbox" disabled="disabled" v-model='col.attributes.primaryKey' /></td>
                        <td>{{ col.attributes.default }}</td>
                    </tr>
                    </tbody>
                </table>
                <div v-if='selectedItem.relations' class="builder-form">
                    <div class="input-title">
                        <label>Table Relations</label>
                    </div>

                </div>
                <table class="table table-striped table-responsive">
                    <thead>
                    <tr>
                        <th class="tbl-status"></th>
                        <th>Name</th>
                        <th>Columns</th>
                        <th>FK Table</th>
                        <th>FK Columns</th>
                        <th>On Update</th>
                        <th>On Delete</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="fk in selectedItem.relations" v-bind:class="{ 'drop-column': fk.drop }">
                        <td v-bind:class="{ migrated: fk.migrated == true, 'not-migrated': fk.migrated == false}" class="tbl-status"></td>
                        <td>{{ fk.index }}</td>
                        <td>{{ fk.column }}</td>
                        <td>{{ fk.fk_table }}</td>
                        <td>{{ fk.fk_column }}</td>
                        <td>{{ fk.on_update ? fk.on_update : '' }}</td>
                        <td>{{ fk.on_delete ? fk.on_delete : '' }}</td>
                    </tr>
                    </tbody>
                </table>

                <div v-if='selectedItem.indexes' class="builder-form">
                    <div class="input-title">
                        <label>Table Indexes</label>
                    </div>

                </div>
                <table class="table table-striped table-responsive">
                    <thead>
                    <tr>
                        <th class="tbl-status"></th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Columns</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="indx in selectedItem.indexes" v-bind:class="{ 'drop-column': indx.drop }">
                        <td v-bind:class="{ migrated: indx.migrated == true, 'not-migrated': indx.migrated == false}" class="tbl-status"></td>
                        <td>{{ indx.index }}</td>
                        <td>{{ indx.type }}</td>
                        <td>{{ indx.columns }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <router-view  name="update_form"></router-view>
        </div>
</template>
<script>
import { mapState } from 'vuex'
import {EnumTypeWarning} from '../blueprint.js'

export default {

    data() {

        return {
            formData : {},
            EnumWarningMsg: EnumTypeWarning
        }
    },


    computed: {

        showBackButton(){
          if(this.$parent.showLeft == false) {
              return true;
          }

          return false;
        },


        ...mapState('database',['selectedItem', 'selectedIndex']),
    },


    methods: {

        goBack() {
            this.$router.push({ path: '/database' })
        },

        goEditPage() {
            this.$router.push({ name: 'db-table-update', params: { key : this.selectedItem.table_name }})
        }

    }
}
</script>
<style lang="sass" scoped>
.toolbar {
    padding: 8px;
    background-color: #ebeaee;

    .btn {
        font-size: .8em;
    }

}
.table {
    font-size: 1em;
    background-color: #FFF;

    .tbl-status {
        width: 10px;
        padding: 0;
    }

    td.migrated {
        background-color: #3f964e;
    }

    td.not-migrated {
        background-color: #c9a900;
    }

    tr.drop-column {
        td.migrated {
            background-color: #cc1214;
        }

        td.not-migrated {
            background-color: #cc1214;
        }

        td {
            color: #cc1214;
            text-decoration: line-through;
        }
    }
}
</style>