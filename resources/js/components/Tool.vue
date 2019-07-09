<template>
    <div>
        <FullCalendar
                ref="fullCalendar"
                :plugins="calendarPlugins"
                :weekends="calendarWeekends"
                :events="calendarEvents"
                default-view="timeGridWeek"

                :header="{
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                }"
                @eventRender="handleEventRender"
                @eventClick="handleEventClick"
                @dateClick="handleDateClick"
        />
    </div>
</template>

<script>
    import FullCalendar from "@fullcalendar/vue";
    import dayGridPlugin from "@fullcalendar/daygrid";
    import timeGridPlugin from "@fullcalendar/timegrid";
    import interactionPlugin from "@fullcalendar/interaction";
    import Tooltip from 'tooltip.js';

    export default {
        props: ['resourceName', 'resourceId', 'field'],
        components: {
            FullCalendar
        },
        data() {
            return {
                calendarPlugins: [
                    dayGridPlugin,
                    timeGridPlugin,
                    interactionPlugin,
                ],
                calendarWeekends: true,
                calendarEvents: [],
                debugStr: []
            };
        },
        methods: {
            toggleWeekends() {
                this.calendarWeekends = !this.calendarWeekends; // update a property
            },
            gotoPast() {
                let calendarApi = this.$refs.fullCalendar.getApi();
                calendarApi.gotoDate("2000-01-01"); // call a method on the Calendar object
            },
            handleDateClick(arg) {
                // arg.dateStr
                if (this.resourceName === 'services'
                    || this.resourceName === 'schedules') {
                    window.open(`/crm/resources/seances/new?viaResource=${this.resourceName === 'schedules' ? 'services' : this.resourceName}&viaResourceId=${this.resourceId}&viaRelationship=seances`, "_blank");
                } else {
                    window.open(`/crm/resources/seances/new`, "_blank");
                }
            },
            handleEventClick(event) {
                if (event.url) {
                    window.open(event.url, "_blank");
                    return false;
                }
            },
            handleEventRender(info) {
                new Tooltip(info.el, {
                    html: true,
                    title: `<h4>${info.event.extendedProps.service}</h4>` +
                        `<p>${info.event.extendedProps.description}</p>` +
                        `<p>${info.event.extendedProps.price} руб.</p>` +
                        `<p>${info.event.extendedProps.duration} минут</p>` +
                        `<p>${info.event.extendedProps.client}</p>`,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            },
        },
        mounted() {
            var seances_promise;
            if (this.resourceName === 'clubs') {
                seances_promise = axios.get(`/v1/clubs/${this.resourceId}/seances`);
            } else if (this.resourceName === 'services'
                || this.resourceName === 'schedules') {
                seances_promise = axios.get(`/v1/services/${this.resourceId}/seances`);
            }

            let prepare_client_data = client_data_json => {
                let result = '';
                for (let a in client_data_json) {
                    if (!!client_data_json[a])
                        result += `<p>${a}: ${client_data_json[a]}</p>`;
                }
                return result
            };

            seances_promise.then(response => {
                this.seances = response.data.result;
                if (this.seances) {
                    this.calendarEvents = [];
                    this.seances.forEach((seance) => {
                        let time = moment(seance.time, ['hh:mm:ss']);
                        let start = moment(seance.date).set({
                            hour: time.get('hour'),
                            minute: time.get('minute')
                        });
                        let end = moment(start).add({
                            minute: seance.duration
                        });
                        let calendarEvent = {
                            title: seance.description,
                            start: start.format(),
                            end: end.format(),
                            extendedProps: {
                                description: seance.description,
                                price: seance.price,
                                duration: seance.duration,
                                service: seance.service.name,
                                client: prepare_client_data(seance.clients[0])
                            },
                            url: '/nova/resources/seances/' + seance.id
                        };
                        this.calendarEvents.push(calendarEvent);
                    })
                }
            });
        },
    }
</script>


<style>
    @import "~@fullcalendar/core/main.css";
    @import "~@fullcalendar/daygrid/main.css";
    @import "~@fullcalendar/timegrid/main.css";

    .tooltip {
        z-index: 1;
        width: 250px;
        color: #252d37;
        background-color: #ffffff;
    }
</style>

