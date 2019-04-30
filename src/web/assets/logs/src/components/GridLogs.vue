<template>
    <div class="grid-logs">
        <el-table v-loading="loading" :data="tableData" style="width: 100%">
            <el-table-column type="index" :index="indexMethod"></el-table-column>
            <el-table-column prop="status" :label="translations.Status" width="180">
                <template slot-scope="props">
                    <el-tag type="success" size="medium" v-if="props.row.status === 'successful'">
                        {{ props.row.status }}
                    </el-tag>
                    <template slot-scope="props" v-else-if="props.row.status === 'failed'">
                        <el-popover
                                placement="top-start"
                                width="200"
                                trigger="hover"
                                :title="translations.Reason"
                                :content="props.row.reason">
                            <el-tag type="danger" size="medium" slot="reference">{{ props.row.status }}</el-tag>
                        </el-popover>
                    </template>
                    <el-tag type="info" size="medium" v-else>{{ props.row.status }}</el-tag>
                </template>
            </el-table-column>
            <el-table-column prop="startTime" :label="translations['Start Date']" width="200"></el-table-column>
            <el-table-column prop="endTime" :label="translations['End Date']" width="200"></el-table-column>
            <el-table-column :label="translations.Duration">
                <template slot-scope="props">
                    {{ props.row.duration }} ms
                </template>
            </el-table-column>
            <el-table-column prop="output" :label="translations.Output" type="expand" width="100">
                <template slot-scope="props">
                    <el-form label-position="left" inline class="demo-table-expand">
                        <div style="background-color: black;">{{ props.row.output }}</div>
                    </el-form>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination style="margin-top: 10px;"
                       @size-change="handleSizeChange"
                       @current-change="handleCurrentChange"
                       :current-page="currentPage"
                       :page-sizes="[20, 50, 100, 500]"
                       :page-size="pageSize"
                       layout="total, sizes, prev, pager, next, jumper"
                       :total="total">
        </el-pagination>
    </div>
</template>

<script lang="ts">
    import {Component, Watch, Vue} from 'vue-property-decorator'

    @Component
    export default class GridLogs extends Vue {
        loading: boolean = true
        tableData: any[] = []
        total: number = 0
        currentPage: number = 1
        pageSize: number = 20
        translations: object = {
            'Status': 'Status',
            'Date': 'Date',
            'Reason': 'Reason',
            'Start Date': 'Start Date',
            'End Date': 'End Date',
            'Duration': 'Duration',
            'Output': 'Output',
        }

        @Watch('$route')
        onChangeRoute() {
            this.fetchLogs()
        }

        created() {
            this.fetchLogs()
        }

        mounted() {
            this.fetchLogs()

            if (typeof ((<any>window).Craft.translations.schedule) !== "undefined") {
                this.translations = (<any>window).Craft.translations.schedule
            }
        }

        indexMethod(index: number) {
            return this.tableData[index].sortOrder
        }

        fetchLogs() {
            let vm = this;

            this.loading = true
            this.tableData = []

            this.$http.post((<any>window).Craft.schedule.logs.api.logs, {
                criteria: {
                    schedule: vm.$route.params['handle'],
                    offset: (vm.currentPage - 1) * vm.pageSize,
                    limit: vm.pageSize,
                }
            }).then(response => {
                vm.loading = false
                vm.total = response.data.total
                vm.tableData = response.data.data
            })
        }

        handleSizeChange(val: number) {
            this.pageSize = val
            this.fetchLogs()
        }

        handleCurrentChange(val: number) {
            this.currentPage = val
            this.fetchLogs()
        }
    }
</script>