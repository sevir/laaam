$(document).ready(function(){
	$('#userTableContainer').jtable({
				title: 'User list',
				actions: {
					listAction: '/cmanager/public/index.php/laaam/get_user',
					createAction: '/cmanager/public/index.php/laaam/create_user',
					updateAction: '/cmanager/public/index.php/laaam/update_user',
					deleteAction: '/cmanager/public/index.php/laaam/delete_user'
				},
				fields: {
					ID: {
						key: true,
						create: false,
						edit: false,
						list: false
					},
					user_login: {
						title: 'Login',
						width: '20%'
					},
					user_pass: {
						title: 'Encrypted password',
						width: '20%'
					},
					user_nicename: {
						title: 'Nice name',
						width: '20%'
					},
					user_email: {
						title: 'Email',
						width: '10%'
					},
					display_name: {
						title: 'Display name',
						width: '10%'
					},
					user_registered: {
						title: 'Registered',
						width: '10%',
						type: 'date',
						create: false,
						edit: false
					},
					can_admin: {
						title: 'Is admin',
						type: 'checkbox',
						values: {'false':'No', 'true':'Yes'},
						defaultValue: 'false',
						list: false,
						create: true,
						edit: true
					}
				}
			});

	$("#userTableContainer").jtable('load');
});