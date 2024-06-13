$(document).ready(function () {
    $('#calendar').fullCalendar({
    events: 'load_activities.php',
    editable: false, 
    eventLimit: true,
    header: {
        left: 'prev,next today',
        center: 'title',
        right: 'month,agendaWeek,agendaDay'
    },
    selectable: true,
    dayClick: function(date, jsEvent, view) {
            console.log(date);
            Swal.fire({
                icon: "success",
                title: "Clicked",
                text: date.format(),
                confirmButtonText: "OK",
            });
        },
    select: function(start, end) {
        console.log(start, end);
        Swal.fire({
            icon: "success",
            title: "Selected",
            text: `${start.format()} ~ ${end.format()}`,
            confirmButtonText: "OK",
        });
    },
    eventClick: function (event) {
        if (confirm('Are you sure you want to delete this event?')) {
            $.ajax({
                url: 'manage_calendar.php',
                type: 'POST',
                data: {
                    action: 'delete',
                    activity_id: event.id
                },
                success: function (response) {
                    $('#calendar').fullCalendar('removeEvents', event._id);
                    alert(response);
                    location.reload();
                }
            });
        }
    }
});


    // 活動加到行事曆
    $(document).on('click', '.add-to-calendar-btn', function () {
        var button = $(this);
        var activityId = button.data('activity-id');
        var action = button.text() === '加入行事曆' ? 'add' : 'delete';

        $.ajax({
            url: 'manage_calendar.php',
            type: 'POST',
            data: {
                action: action,
                activity_id: activityId
            },
            success: function (response) {
                alert(response);
                $('#calendar').fullCalendar('refetchEvents'); // 重新載入行事曆

                // 更新button的字
                if (action === 'add') {
                    button.text('取消加入此活動');
                } else {
                    button.text('加入行事曆');
                }
            }
        });
    });
});