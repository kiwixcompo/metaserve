
                                        // Set default to prog1 on load
                                        document.addEventListener("DOMContentLoaded", function() {
                                            document.getElementById('programme_id').value = '<?= $prog1_id ?>';
                                        });
                                        
                                        function setProgramme(type) {
                                            document.getElementById('programme_id').value = (type === 1) ? '<?= $prog1_id ?>' : '<?= $prog2_id ?>';
                                        }
                                    