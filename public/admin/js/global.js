
	function getAllStudentByBranch(urlGet,contentData,SelectedId=null){//ok
		dojo.xhrPost({
			url:urlGet,	
			handleAs:"json",
			content:contentData,
			load: function(data) {
				studentStore  = getDataStorefromJSON('id','name', data);		
				dijit.byId('studentId').set('store',studentStore);  
			    if(SelectedId!=null){
			    	 dijit.byId('studentId').attr('value',SelectedId);
			    }
			},
			error: function(err){
			}
		});
	}
	
	function getAllGroupByBranch(urlGetgroup,contentData,SelectedId=null){
		dojo.xhrPost({
			url:urlGetgroup,	
			handleAs:"json",
			content:contentData,
			load: function(data) {
				groupStore  = getDataStorefromJSON('id','name', data);		
			    dijit.byId('groupId').set('store',groupStore);
			    if(SelectedId!=null){
			    	 dijit.byId('groupId').attr('value',SelectedId);
			    }
			},
			error: function(err){
			}
		});
	}
	function getGradebyDegree(urlGetgrade,contentData,SelectedId=null){
		dojo.xhrPost({
			url:urlGetgrade,	
			handleAs:"json",
			content:contentData,
			load: function(data) {
				gradeStore  = getDataStorefromJSON('id','name', data);		
			    dijit.byId('gradeId').set('store',gradeStore);
			    if(SelectedId!=null){
			    	 dijit.byId('gradeId').attr('value',SelectedId);
			    }
			},
			error: function(err){
			}
		});
	}
	function getAllSubjectByGroup(urlGetSubject,contentData,SelectedId=null){
		dojo.xhrPost({
			url:urlGetSubject,	
			handleAs:"json",
			content:contentData,
			load: function(data) {
				subjectStore  = getDataStorefromJSON('id','name', data);		
			    dijit.byId('groupId').set('store',subjectStore);
			    if(SelectedId!=null){
			    	dijit.byId('subjectId').attr('value',SelectedId);
			    }
			},
			error: function(err){
			}
		});
	}
	function getAllItemType(urlGet,contentData,SelectedId=null){
		dojo.xhrPost({
			url:urlGet,	
			handleAs:"json",
			content:contentData,
			load: function(data) {
				studentStore  = getDataStorefromJSON('id','name', data);		
				dijit.byId('studentId').set('store',studentStore);  
			    if(SelectedId!=null){
			    	 dijit.byId('studentId').attr('value',SelectedId);
			    }
			},
			error: function(err){
			}
		});
	}
	function getAllItemIdByType(urlGet,contentData,SelectedId=null){//get item detail
		dojo.xhrPost({
			url:urlGet,	
			handleAs:"json",
			content:contentData,
			load: function(data) {
				Store  = getDataStorefromJSON('id','name', data);		
				dijit.byId('itemId').set('store',Store);  
			    if(SelectedId!=null){
			    	 dijit.byId('itemId').attr('value',SelectedId);
			    }
			},
			error: function(err){
			}
		});
	}
	function getAllAcademicByBranch(urlGet,contentData,SelectedId=null){//done
		dojo.xhrPost({
			url: urlGet,
			content:contentData,
			handleAs:"json",
			load: function(data){
				academic_store  = getDataStorefromJSON('id','name', data);
			    dijit.byId('study_year').set('store',academic_store);  
			    if(SelectedId!=null){
			    	 dijit.byId('study_year').attr('value',SelectedId);
			    }
			},
			error: function(err) {
			}
		});
	}
	function getAllYear(urlGet,contentData,SelectedId=null,strControl=null){//done
		dojo.xhrPost({
			url: urlGet,
			content:contentData,
			handleAs:"json",
			load: function(data){
				year_store  = getDataStorefromJSON('id','name', data);
				if(strControl!=null){
					 dijit.byId(strControl).set('store',year_store);  
						if(SelectedId!=null){
							dijit.byId(strControl).attr('value',SelectedId);
						}
				}else{
					  dijit.byId('academic_year').set('store',year_store);  
					  if(SelectedId!=null){
						 dijit.byId('academic_year').attr('value',SelectedId);
					  }
				}
			},
			error: function(err) {
			}
		});
	}
	

