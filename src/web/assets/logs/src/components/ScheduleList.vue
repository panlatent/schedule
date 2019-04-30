<template>
    <div class="schedule-list">
        <div class="search">
            <el-input placeholder="Search all schedules" learable suffix-icon="el-icon-search"
                      size="small" v-model="search"></el-input>
        </div>
        <div class="list" v-loading="loading">
            <el-card class="box-card" shadow="never" v-bind:class="{passed: schedule.status, failed: !schedule.status}"
                     v-for="schedule in schedules">
                <h2>
                    <router-link :to="{ name: 'logs', params: { handle: schedule.handle }}">
                    <span>
                        <i class="el-icon-check" v-if="schedule.status === true"></i>
                        <i class="el-icon-check" v-else-if="schedule.status === false"></i>
                        <i class="el-icon-close" v-else="schedule.status"></i>
                    </span>
                        <span>{{ schedule.name }}</span>
                    </router-link>
                </h2>
                <p class="right"><i class="el-icon-caret-right"></i> {{ schedule.total }}</p>
                <p><i class="el-icon-time"></i> Duration: {{ schedule.duration }} ms</p>
                <p><i class="el-icon-date"></i> Finished: {{ schedule.finished }}</p>
            </el-card>
        </div>
    </div>
</template>

<script lang="ts">
    import {Component, Watch, Vue} from 'vue-property-decorator'

    @Component
    export default class ScheduleList extends Vue {
        loading: boolean = true
        schedules: object[] = []
        search: string = ''

        @Watch('search')
        onSearch() {
            this.fetchSchedules()
        }

        mounted() {
            this.fetchSchedules()
        }

        fetchSchedules() {
            this.$http.post((<any>window).Craft.schedule.logs.api.schedules, {
                criteria: {
                    search: this.search
                }
            }).then(response => {
                this.loading = false
                this.schedules = response.data.data
            })
        }
    }
</script>

<style scoped>
    .schedule-list {
        background-color: #f1f1f1;
    }

    .schedule-list .search {
        position: relative;
        height: 7.2em;
        background-color: #fff;
    }

    .schedule-list .search .el-input {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        bottom: 0;
        margin: auto;
        width: 90%;
        height: 33px;
        border-radius: 4px;
    }

    .schedule-list .el-card {
        margin-bottom: 6px;
    }

    .schedule-list .el-card h2 {
        display: inline-block;
        width: 70%;
        font-weight: 400;
    }

    .schedule-list .el-card p.right {
        display: inline-block;
        width: 20%;
        margin: 0;
    }

    .schedule-list .el-card.passed {
        background: linear-gradient(to right, #39aa56 0, #39aa56 8px, #fff 8px, #fff 100%) no-repeat;
    }

    .schedule-list .el-card.passed a {
        color: #39aa56;
    }

    .schedule-list .el-card.passed p.right {
        color: #39aa56;
    }

    .schedule-list .el-card.failed {
        background: linear-gradient(to right, #db4545 0, #db4545 8px, #fff 8px, #fff 100%) no-repeat;
    }

    .schedule-list .el-card.failed a {
        color: #db4545;
    }

    .schedule-list .el-card.failed p.right {
        color: #db4545;
    }
</style>