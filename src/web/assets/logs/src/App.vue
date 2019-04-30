<template>
    <el-container>
        <el-aside>
            <schedule-list></schedule-list>
        </el-aside>
        <el-main>
            <router-view></router-view>
        </el-main>
    </el-container>
</template>

<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import ScheduleList from './components/ScheduleList.vue';

    @Component({
        components: {
            ScheduleList,
        }
    })
    export default class App extends Vue {

    }
</script>

<style>
    .el-aside {
        border-right: 1px solid #EBEEF5;
    }
</style>
