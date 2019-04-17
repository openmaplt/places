create or replace function kol_update_status(p_uid bigint, p_token text, p_status text) returns int as $$
begin
  delete from kol_lankymas where uid = p_uid and token = p_token;
  if p_status != 'n' then
    insert into kol_lankymas values (null, p_token, p_uid, p_status, current_timestamp);
  end if;
  return 0;
end
$$ language plpgsql;